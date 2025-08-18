<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Service\Image\SaveImage;
use App\Http\Service\Image\DeleteImage;
use App\Services\ReferralCommissionService;
use App\Models\PurchaseRequest;
use App\Models\MonthlyUserPoint;
use App\Models\User;

class ProductService
{
    use SaveImage, DeleteImage;

    protected ReferralCommissionService $referralService;

    public function __construct(ReferralCommissionService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Obtener todos los productos activos
     */
    public function getAll()
    {
        return Product::with('course')->where('status', 'active')->latest()->get();
    }

    /**
     * Obtener producto por ID
     */
    public function getById(int $id): ?Product
    {
        return Product::with('course')->find($id);
    }

    /**
     * Crear un nuevo producto
     */
    public function store(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            if (request()->hasFile('image') && request()->file('image') instanceof UploadedFile) {
                $folder = ($data['type'] === 'course') ? 'courses' : 'products';

                $path = $this->upload(request()->file('image'), $folder);
                $data['image_path'] = $path;
            }

            $product = Product::create($data);

            if ($product->type === 'course') {
                Course::create([
                    'product_id' => $product->id,
                    'duration' => $data['duration'] ?? '',
                    'tutor' => $data['tutor'] ?? '',
                    'modality' => $data['modality'] ?? null,
                    'schedule' => $data['schedule'] ?? null,
                ]);
            }

            return $product->load('course');
        });
    }

    /**
     * Actualizar un producto existente
     */
    public function update(Product $product, Request $request): Product
    {
        return DB::transaction(function () use ($product, $request) {
            $data = $request->except('image'); 

            if ($request->hasFile('image') && $request->file('image') instanceof UploadedFile) {
                if ($product->image_path) {
                    $this->deleteImage($product->image_path);
                }

                $folder = ($product->type === 'course') ? 'courses' : 'products';

                $path = $this->upload($request->file('image'), $folder);
                $data['image_path'] = $path;
            }

            if (!empty($data)) {
                $product->update($data);
            }

            if ($product->type === 'course' &&
                $request->hasAny(['duration', 'tutor', 'modality', 'schedule'])) {
                $product->course()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'duration' => $request->input('duration', $product->course->duration ?? ''),
                        'tutor' => $request->input('tutor', $product->course->tutor ?? ''),
                        'modality' => $request->input('modality', $product->course->modality ?? null),
                        'schedule' => $request->input('schedule', $product->course->schedule ?? null),
                    ]
                );
            }

            return $product->load('course');
        });
    }

    /**
     * Procesar compra de producto por un usuario
     */
    public function purchaseProduct(User $user, Product $product, int $quantity = 1, string $paymentMethod = 'atipay'): bool
    {
        return DB::transaction(function () use ($user, $product, $quantity, $paymentMethod) {
            if ($product->stock < $quantity) {
                throw new \Exception('Stock insuficiente.');
            }

            $totalPrice = $product->price * $quantity;
            $totalPoints = $product->points_earned * $quantity;

            if ($paymentMethod === 'atipay') {
                if ($user->atipay_money < $totalPrice) {
                    throw new \Exception('Saldo Atipay insuficiente.');
                }

                // Se descuenta desde atipay_money
                $user->atipay_money -= $totalPrice;
                $user->accumulated_points += $totalPoints; 
                $user->save();

                $this->referralService->process($user, $totalPoints, 'purchase');
            } elseif ($paymentMethod === 'points') {
                if ($user->accumulated_points < $totalPrice) {
                    throw new \Exception('Puntos insuficientes.');
                }

                $user->accumulated_points -= $totalPrice;
                $user->save();

            } else {
                throw new \Exception('Método de pago inválido.');
            }

            $product->stock -= $quantity;
            $product->save();

            return true;
        });
    }

    public function requestPurchase(User $user, Product $product, int $quantity, string $paymentMethod): PurchaseRequest
    {
        return DB::transaction(function () use ($user, $product, $quantity, $paymentMethod) {
            if ($product->stock < $quantity) {
                throw new \Exception('Stock insuficiente.');
            }

            $totalPrice  = $product->price * $quantity;

            // Descuento inmediato del dinero/puntos
            if ($paymentMethod === 'atipay') {
                if ($user->atipay_money < $totalPrice) {
                    throw new \Exception('Saldo Atipay insuficiente.');
                }
                $user->atipay_money -= $totalPrice;
                $user->save();
            } elseif ($paymentMethod === 'points') {
                if ($user->accumulated_points < $totalPrice) {
                    throw new \Exception('Puntos insuficientes.');
                }
                $user->accumulated_points -= $totalPrice;
                $user->save();
            } else {
                throw new \Exception('Método de pago inválido.');
            }

            // Reducir stock
            $product->stock -= $quantity;
            $product->save();

            // Crear solicitud pendiente
            return PurchaseRequest::create([
                'user_id'       => $user->id,
                'product_id'    => $product->id,
                'quantity'      => $quantity,
                'payment_method'=> $paymentMethod,
                'status'        => 'pending',
            ]);
        });
    }


    public function getAllPurchaseRequests()
    {
        return PurchaseRequest::with(['user', 'product'])->latest()->get();
    }

    public function getUserPurchaseRequests(User $user)
    {
        return PurchaseRequest::with('product')->where('user_id', $user->id)->latest()->get();
    }

    /**
     * Aprobar una solicitud de compra pendiente.
     */
    public function approvePurchase(int $id, ?string $adminMessage = null): PurchaseRequest
    {
        return DB::transaction(function () use ($id, $adminMessage) {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            if ($purchaseRequest->status !== 'pending') {
                throw new \Exception('La solicitud ya fue procesada.');
            }

            $product = $purchaseRequest->product;
            $user    = $purchaseRequest->user;

            $totalPoints = $product->points_earned * $purchaseRequest->quantity;

            // Sumar puntos y procesar referidos
            if ($purchaseRequest->payment_method === 'atipay') {
                $user->accumulated_points += $totalPoints;
                $user->save();

                $this->referralService->process($user, $totalPoints, 'purchase');
            }

            $purchaseRequest->status = 'approved';
            $purchaseRequest->admin_message = $adminMessage;
            $purchaseRequest->save();

            return $purchaseRequest->fresh();
        });
    }

    /**
     * Rechazar una solicitud de compra pendiente.
     */
    public function rejectPurchase(int $id, ?string $adminMessage = null): PurchaseRequest
    {
        return DB::transaction(function () use ($id, $adminMessage) {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            if ($purchaseRequest->status !== 'pending') {
                throw new \Exception('La solicitud ya fue procesada.');
            }

            $product = $purchaseRequest->product;
            $user    = $purchaseRequest->user;
            $totalPrice  = $product->price * $purchaseRequest->quantity;

            // Devolver dinero/puntos
            if ($purchaseRequest->payment_method === 'atipay') {
                $user->atipay_money += $totalPrice;
                $user->save();
            } elseif ($purchaseRequest->payment_method === 'points') {
                $user->accumulated_points += $totalPrice;
                $user->save();
            }

            // Restaurar stock
            $product->stock += $purchaseRequest->quantity;
            $product->save();

            $purchaseRequest->status = 'rejected';
            $purchaseRequest->admin_message = $adminMessage;
            $purchaseRequest->save();

            return $purchaseRequest->fresh();
        });
    }

    /**
     * Eliminar un producto y su imagen
     */
    public function delete(Product $product): bool
    {
        if ($product->image_path) {
            $this->deleteImage($product->image_path);
        }

        if ($product->type === 'course') {
            $product->course()->delete();
        }

        return $product->delete();
    }
}
