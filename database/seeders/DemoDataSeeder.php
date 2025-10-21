<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles and permissions
        $this->createRolesAndPermissions();

        // Create demo users
        $this->createDemoUsers();

        // Create categories
        $this->createCategories();

        // Create manufacturers
        $this->createManufacturers();

        // Create medicines
        $this->createMedicines();

        // Create customers
        $this->createCustomers();

        // Create stock entries
        $this->createStockEntries();

        // Create sample invoices
        $this->createSampleInvoices();
    }

    private function createRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'view-medicines',
            'create-medicines',
            'edit-medicines',
            'delete-medicines',
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',
            'view-stocks',
            'create-stocks',
            'edit-stocks',
            'delete-stocks',
            'view-purchases',
            'create-purchases',
            'edit-purchases',
            'delete-purchases',
            'view-reports',
            'manage-users',
            'manage-system',
            'manage-data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $managerRole->givePermissionTo([
            'view-medicines',
            'create-medicines',
            'edit-medicines',
            'view-customers',
            'create-customers',
            'edit-customers',
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'view-stocks',
            'create-stocks',
            'edit-stocks',
            'view-purchases',
            'create-purchases',
            'edit-purchases',
            'view-reports',
        ]);

        $cashierRole->givePermissionTo([
            'view-medicines',
            'view-customers',
            'view-invoices',
            'create-invoices',
            'view-stocks',
            'view-purchases',
        ]);
    }

    private function createDemoUsers()
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@pharmacare.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@pharmacare.com',
                'password' => bcrypt('password'),
                'role' => 'manager',
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@pharmacare.com',
                'password' => bcrypt('password'),
                'role' => 'cashier',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );
            $user->assignRole($userData['role']);
        }
    }

    private function createCategories()
    {
        $categories = [
            ['name' => 'Antibiotics', 'description' => 'Antibacterial medications'],
            ['name' => 'Pain Relief', 'description' => 'Pain management medications'],
            ['name' => 'Cardiovascular', 'description' => 'Heart and blood vessel medications'],
            ['name' => 'Diabetes', 'description' => 'Diabetes management medications'],
            ['name' => 'Respiratory', 'description' => 'Respiratory system medications'],
            ['name' => 'Gastrointestinal', 'description' => 'Digestive system medications'],
            ['name' => 'Neurological', 'description' => 'Nervous system medications'],
            ['name' => 'Dermatology', 'description' => 'Skin condition medications'],
            ['name' => 'Vitamins', 'description' => 'Vitamin supplements'],
            ['name' => 'Supplements', 'description' => 'Dietary supplements'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }

    private function createManufacturers()
    {
        $manufacturers = [
            ['name' => 'Pfizer', 'contact_person' => 'John Smith', 'email' => 'contact@pfizer.com', 'phone' => '+1-555-0123'],
            ['name' => 'Johnson & Johnson', 'contact_person' => 'Sarah Johnson', 'email' => 'contact@jnj.com', 'phone' => '+1-555-0124'],
            ['name' => 'Novartis', 'contact_person' => 'Michael Brown', 'email' => 'contact@novartis.com', 'phone' => '+1-555-0125'],
            ['name' => 'Roche', 'contact_person' => 'Emily Davis', 'email' => 'contact@roche.com', 'phone' => '+1-555-0126'],
            ['name' => 'Merck & Co.', 'contact_person' => 'David Wilson', 'email' => 'contact@merck.com', 'phone' => '+1-555-0127'],
            ['name' => 'GlaxoSmithKline', 'contact_person' => 'Lisa Anderson', 'email' => 'contact@gsk.com', 'phone' => '+1-555-0128'],
            ['name' => 'Sanofi', 'contact_person' => 'Robert Taylor', 'email' => 'contact@sanofi.com', 'phone' => '+1-555-0129'],
            ['name' => 'AbbVie', 'contact_person' => 'Jennifer Martinez', 'email' => 'contact@abbvie.com', 'phone' => '+1-555-0130'],
        ];

        foreach ($manufacturers as $manufacturer) {
            Manufacturer::firstOrCreate(['name' => $manufacturer['name']], $manufacturer);
        }
    }

    private function createMedicines()
    {
        $medicines = [
            [
                'name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'strength' => '500mg',
                'box_size' => 10,
                'price' => 25.50,
                'manufacturer_price' => 20.00,
                'unit' => 'tablet',
                'category' => 'Pain Relief',
                'manufacturer' => 'Pfizer',
            ],
            [
                'name' => 'Ibuprofen',
                'generic_name' => 'Ibuprofen',
                'strength' => '400mg',
                'box_size' => 20,
                'price' => 45.00,
                'manufacturer_price' => 35.00,
                'unit' => 'tablet',
                'category' => 'Pain Relief',
                'manufacturer' => 'Johnson & Johnson',
            ],
            [
                'name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'strength' => '500mg',
                'box_size' => 21,
                'price' => 120.00,
                'manufacturer_price' => 95.00,
                'unit' => 'capsule',
                'category' => 'Antibiotics',
                'manufacturer' => 'Novartis',
            ],
            [
                'name' => 'Metformin',
                'generic_name' => 'Metformin',
                'strength' => '500mg',
                'box_size' => 30,
                'price' => 85.00,
                'manufacturer_price' => 65.00,
                'unit' => 'tablet',
                'category' => 'Diabetes',
                'manufacturer' => 'Roche',
            ],
            [
                'name' => 'Lisinopril',
                'generic_name' => 'Lisinopril',
                'strength' => '10mg',
                'box_size' => 30,
                'price' => 95.00,
                'manufacturer_price' => 75.00,
                'unit' => 'tablet',
                'category' => 'Cardiovascular',
                'manufacturer' => 'Merck & Co.',
            ],
            [
                'name' => 'Vitamin D3',
                'generic_name' => 'Cholecalciferol',
                'strength' => '1000 IU',
                'box_size' => 60,
                'price' => 35.00,
                'manufacturer_price' => 25.00,
                'unit' => 'tablet',
                'category' => 'Vitamins',
                'manufacturer' => 'GlaxoSmithKline',
            ],
            [
                'name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'strength' => '20mg',
                'box_size' => 14,
                'price' => 65.00,
                'manufacturer_price' => 50.00,
                'unit' => 'capsule',
                'category' => 'Gastrointestinal',
                'manufacturer' => 'Sanofi',
            ],
            [
                'name' => 'Cetirizine',
                'generic_name' => 'Cetirizine',
                'strength' => '10mg',
                'box_size' => 30,
                'price' => 40.00,
                'manufacturer_price' => 30.00,
                'unit' => 'tablet',
                'category' => 'Respiratory',
                'manufacturer' => 'AbbVie',
            ],
        ];

        foreach ($medicines as $medicineData) {
            $category = Category::where('name', $medicineData['category'])->first();
            $manufacturer = Manufacturer::where('name', $medicineData['manufacturer'])->first();

            Medicine::firstOrCreate(
                ['name' => $medicineData['name']],
                [
                    'product_id' => 'MED' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'generic_name' => $medicineData['generic_name'],
                    'strength' => $medicineData['strength'],
                    'box_size' => $medicineData['box_size'],
                    'price' => $medicineData['price'],
                    'manufacturer_price' => $medicineData['manufacturer_price'],
                    'unit' => $medicineData['unit'],
                    'category_id' => $category->id,
                    'manufacturer_id' => $manufacturer->id,
                    'status' => true,
                ]
            );
        }
    }

    private function createCustomers()
    {
        $customers = [
            [
                'name' => 'John Doe',
                'mobile' => '1234567890',
                'email' => 'john.doe@email.com',
                'address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'USA',
            ],
            [
                'name' => 'Jane Smith',
                'mobile' => '2345678901',
                'email' => 'jane.smith@email.com',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip' => '90210',
                'country' => 'USA',
            ],
            [
                'name' => 'Mike Johnson',
                'mobile' => '3456789012',
                'email' => 'mike.johnson@email.com',
                'address' => '789 Pine Road',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60601',
                'country' => 'USA',
            ],
            [
                'name' => 'Sarah Williams',
                'mobile' => '4567890123',
                'email' => 'sarah.williams@email.com',
                'address' => '321 Elm Street',
                'city' => 'Houston',
                'state' => 'TX',
                'zip' => '77001',
                'country' => 'USA',
            ],
            [
                'name' => 'David Brown',
                'mobile' => '5678901234',
                'email' => 'david.brown@email.com',
                'address' => '654 Maple Drive',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'zip' => '85001',
                'country' => 'USA',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['email' => $customer['email']], $customer);
        }
    }

    private function createStockEntries()
    {
        $medicines = Medicine::all();

        foreach ($medicines as $medicine) {
            // Create 2-3 stock entries per medicine
            $stockCount = rand(2, 3);

            for ($i = 0; $i < $stockCount; $i++) {
                Stock::create([
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'expiry_date' => now()->addMonths(rand(6, 24)),
                    'quantity' => rand(50, 500),
                    'min_stock_level' => rand(10, 50),
                    'max_stock_level' => rand(500, 1000),
                    'purchase_price' => $medicine->manufacturer_price + rand(0, 5),
                    'selling_price' => $medicine->price,
                    'supplier' => $medicine->manufacturer->name,
                    'notes' => 'Demo stock entry',
                    'is_active' => true,
                ]);
            }
        }
    }

    private function createSampleInvoices()
    {
        $customers = Customer::all();
        $medicines = Medicine::all();
        $user = User::where('email', 'admin@pharmacare.com')->first();

        // Create 10 sample invoices
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $invoiceDate = now()->subDays(rand(1, 30));

            $invoice = Invoice::create([
                'invoice_id' => 'INV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'date' => $invoiceDate,
                'invoice_no' => rand(1000, 9999),
                'total_amount' => 0, // Will be calculated
                'total_tax' => 0,
                'previous_due' => 0,
                'paid_amount' => 0,
                'due_amount' => 0,
                'total_discount' => 0,
                'invoice_discount' => 0,
                'user_id' => $user->id,
                'payment_type' => ['cash', 'bank', 'credit'][rand(0, 2)],
                'status' => true,
            ]);

            // Create 2-5 items per invoice
            $itemCount = rand(2, 5);
            $totalAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $medicine = $medicines->random();
                $quantity = rand(1, 5);
                $rate = $medicine->price;
                $discount = rand(0, 10);
                $itemTotal = ($quantity * $rate) - $discount;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => 'BATCH' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'discount' => $discount,
                    'total_amount' => $itemTotal,
                ]);

                $totalAmount += $itemTotal;
            }

            // Update invoice totals
            $tax = $totalAmount * 0.1; // 10% tax
            $finalTotal = $totalAmount + $tax;
            $paidAmount = rand(0, $finalTotal);

            $invoice->update([
                'total_amount' => $finalTotal,
                'total_tax' => $tax,
                'paid_amount' => $paidAmount,
                'due_amount' => $finalTotal - $paidAmount,
            ]);
        }
    }
}
