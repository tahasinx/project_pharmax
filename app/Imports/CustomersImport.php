<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $updateExisting;

    public function __construct(bool $updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        $customerData = [
            'name' => $row['name'],
            'mobile' => $row['mobile'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'state' => $row['state'] ?? null,
            'zip' => $row['zip'] ?? null,
            'country' => $row['country'] ?? null,
            'status' => isset($row['status']) ? (bool) $row['status'] : true,
        ];

        if ($this->updateExisting) {
            return Customer::updateOrCreate(
                ['email' => $row['email']],
                $customerData
            );
        }

        return new Customer($customerData);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
        ];
    }
}
