<?php

namespace Database\Seeders;

use App\Models\ZipCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ZipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $files = [
            'counties_import.csv',
            'place_names_import.csv',
            'zip_codes_import.csv',
        ];

        foreach ($files as $file) {
            $this->command->info("\n\nImporting data from {$file}...\n");

            $filePath = database_path($file);
            $handle = fopen($filePath, 'r');
            $bar = $this->command->getOutput()->createProgressBar(count(file($filePath)) - 1);
            $bar->start();

            fgetcsv($handle, separator: ','); // Skip header row
            while (($data = fgetcsv($handle, separator: ',')) !== false) {
                match ($file) {
                    'counties_import.csv' => $this->import($data, new \App\Models\County()),
                    'place_names_import.csv' => $this->import($data, new \App\Models\PlaceName()),
                    'zip_codes_import.csv' => $this->import($data, new \App\Models\ZipCode()),
                };
                $bar->advance();
            }
            fclose($handle);
            $bar->finish();
        }
        $this->command->info("\n\nZip codes, place names and counties imported successfully.");
    }

    private function import(array $data, Model $model): void
    {
        if ($model instanceof ZipCode && $data[3] === "") {
            $data[3] = null;
        }
        $columns = Schema::getColumnListing($model->getTable());
        $model::create(array_combine($columns, $data));
    }
}
