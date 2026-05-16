<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //Akun Admin (Bypass fillable untuk set role)
        $admin = new User();
        $admin->name = 'Admin Rusunawa';
        $admin->email = 'admin@rusunawa.com';
        $admin->password = Hash::make('password123');
        $admin->role = 'admin'; 
        $admin->phone_number = '081122334455';
        $admin->save();

        //Akun Penyewa (Mahasiswa)
        $penyewa = new User();
        $penyewa->name = 'Mahasiswa Untan';
        $penyewa->email = 'penyewa@student.untan.ac.id';
        $penyewa->password = Hash::make('password123');
        $penyewa->role = 'penyewa';
        $penyewa->identity_type = 'NIM';
        $penyewa->identity_number = 'H105123456'; // Contoh format NIM
        $penyewa->phone_number = '089988776655';
        $penyewa->save();

        $this->call([
            RoomSeeder::class,
        ]);
    }
}
