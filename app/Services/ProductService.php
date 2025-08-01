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
    public function purchaseProduct(User $user, Product $product, int $quantity = 1): bool
    {
        return DB::transaction(function () use ($user, $product, $quantity) {
            if ($product->stock < $quantity) {
                throw new \Exception('Stock insuficiente.');
            }

            $totalPrice = $product->price * $quantity;
            $totalPoints = $product->points * $quantity;

            // Verifica si el usuario tiene saldo suficiente
            if ($user->atipay_store_balance < $totalPrice) {
                throw new \Exception('Saldo insuficiente.');
            }

            // Descuenta el saldo y stock
            $user->atipay_store_balance -= $totalPrice;
            $user->save();

            $product->stock -= $quantity;
            $product->save();

            // Procesar comisiones
            $this->referralService->process($user, $totalPoints, 'purchase');

            return true;
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
