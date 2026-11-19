<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        // Pindahkan spesifikasi lama dari JSON ke tabel baru.
        DB::table('products')
            ->select('id', 'specifications')
            ->whereNotNull('specifications')
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $data = json_decode($product->specifications, true);

                    if (!is_array($data)) {
                        continue;
                    }

                    $rows = [];

                    foreach ($data as $name => $value) {
                        $name = trim((string) $name);

                        if ($name === '') {
                            continue;
                        }

                        if (is_array($value)) {
                            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                        } elseif (is_bool($value)) {
                            $value = $value ? 'Ya' : 'Tidak';
                        } elseif ($value === null) {
                            $value = '';
                        } else {
                            $value = (string) $value;
                        }

                        $rows[] = [
                            'product_id' => $product->id,
                            'name' => $name,
                            'value' => $value,
                            'sort_order' => count($rows),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($rows)) {
                        DB::table('product_specifications')->insert($rows);
                    }
                }
            });

        // Kolom JSON lama dihapus setelah datanya dipindahkan.
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('specifications');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('specifications')->nullable();
        });

        // Kembalikan spesifikasi ke format JSON jika rollback.
        DB::table('products')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $rows = DB::table('product_specifications')
                        ->where('product_id', $product->id)
                        ->orderBy('sort_order')
                        ->get();

                    $data = [];

                    foreach ($rows as $row) {
                        $data[$row->name] = $row->value;
                    }

                    if (!empty($data)) {
                        DB::table('products')
                            ->where('id', $product->id)
                            ->update([
                                'specifications' => json_encode(
                                    $data,
                                    JSON_UNESCAPED_UNICODE
                                ),
                            ]);
                    }
                }
            });

        Schema::dropIfExists('product_specifications');
    }
};
