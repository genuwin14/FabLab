<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        // The machines come from the tech vendor, the STANLEY hand tools from
        // the general merchandiser — so the register can say who to call when
        // a unit goes back for repair.
        $techSupply = Supplier::where('name', 'Tech Supply Co.')->value('supplier_id');
        $genMerch = Supplier::where('name', 'General Merchandise Inc.')->value('supplier_id');

        $items = [
            ['name' => 'Mug Press',                'brand' => 'CUYI Model - Semi Automatic Single Unit', 'property_no' => 'ICS-06-01-21-OME-MP-05-136-1',  'date_acquired' => '2021-06-01', 'cost' => 3750.00,   'status' => 'Non-Serviceable'],
            ['name' => 'Sublimation Printer',      'brand' => 'EPSON L130 SN:VJ6K322561',                'property_no' => 'ICS-06-01-21-ICT-PRI-05-136-1', 'date_acquired' => '2021-06-01', 'cost' => 10200.00,  'status' => 'Serviceable'],
            ['name' => 'Heat Press',               'brand' => 'A3 Model-CUYIGY1031800W220V',             'property_no' => 'ICS-06-01-21-OME-HP-05-136-1',  'date_acquired' => '2021-06-01', 'cost' => 13470.00,  'status' => 'Serviceable'],
            ['name' => 'Lanyard Heat Press',       'brand' => 'Polaris Model: CHINT NXB-63 C32',         'property_no' => '12-03-21-PE-HPM-05-412-1',      'date_acquired' => '2021-12-03', 'cost' => 36000.00,  'status' => 'Serviceable'],
            ['name' => 'Cap Heat Press Machine',   'brand' => 'CUYI Heavy Duty',                         'property_no' => 'ICS-12-06-21-PE-HPM-05-348-1',  'date_acquired' => '2021-12-06', 'cost' => 9996.00,   'status' => 'Serviceable'],
            ['name' => 'Cutter Plotter Machine',   'brand' => 'CAMEO 4, 12 inches, 012F214673',          'property_no' => '12-06-21-OME-CPM-05-413-1',     'date_acquired' => '2021-12-06', 'cost' => 24990.00,  'status' => 'Serviceable'],
            ['name' => 'Direct to Film Printer',   'brand' => 'L1800',                                   'property_no' => '03-31-22-ICT-PRI-05-085-25',    'date_acquired' => '2022-03-31', 'cost' => 60100.00,  'status' => 'Returned to supplier for repair'],
            ['name' => 'Melt Pro Heater',          'brand' => null,                                      'property_no' => '03-31-22-OME-HTR-05-085-1',     'date_acquired' => '2022-03-31', 'cost' => 15000.00,  'status' => 'Serviceable'],
            ['name' => 'L1800 Printer',            'brand' => 'Sublimation Ink',                         'property_no' => '03-31-22-ICT-PRI-05-085-23',    'date_acquired' => '2022-03-31', 'cost' => 104000.00, 'status' => 'Serviceable'],
            ['name' => 'L1800 Printer',            'brand' => 'Pigment Ink',                             'property_no' => '03-31-22-ICT-PRI-05-085-24',    'date_acquired' => '2022-03-31', 'cost' => 61700.00,  'status' => 'Serviceable'],
            ['name' => 'Mug Press Printer',        'brand' => 'Sapphire',                                'property_no' => 'PS-2022-11500',                 'date_acquired' => '2022-03-31', 'cost' => 20000.00,  'status' => 'Non-Serviceable'],
            ['name' => 'Epson L121',               'brand' => 'Sublimation Ink',                         'property_no' => null,                            'date_acquired' => '2023-11-11', 'cost' => 5700.00,   'status' => 'Serviceable'],
            ['name' => 'Epson L130',               'brand' => 'Sublimation Ink',                         'property_no' => 'PS-2021-02675',                 'date_acquired' => '2021-06-01', 'cost' => 10200.00,  'status' => 'Serviceable'],
            ['name' => 'Epson L14150',             'brand' => 'Epson',                                   'property_no' => 'PS-2024-00513',                 'date_acquired' => '2024-02-15', 'cost' => 43500.00,  'status' => 'Serviceable'],
            ['name' => '3D Printer',               'brand' => 'Creality Ender III',                      'property_no' => 'PS-2023-00764',                 'date_acquired' => '2023-01-25', 'cost' => 37200.00,  'status' => 'Serviceable'],
            ['name' => '3D Printer',               'brand' => 'Creality Ender III',                      'property_no' => 'PS-2023-00765',                 'date_acquired' => '2023-01-25', 'cost' => 37200.00,  'status' => 'Serviceable'],
            ['name' => '3D Printer',               'brand' => 'Creality Ender III',                      'property_no' => 'PS-2023-00766',                 'date_acquired' => '2023-01-25', 'cost' => 37200.00,  'status' => 'Serviceable'],
            ['name' => '3D Resin Printer',         'brand' => 'Creality Halot',                          'property_no' => 'PS-2023-00767',                 'date_acquired' => '2023-01-25', 'cost' => 35290.00,  'status' => 'Serviceable'],
            ['name' => '3D Resin Printer',         'brand' => 'Creality Halot',                          'property_no' => 'PS-2023-00768',                 'date_acquired' => '2023-01-25', 'cost' => 35290.00,  'status' => 'Serviceable'],
        ];

        foreach ($items as $item) {
            Equipment::create($item + ['supplier_id' => $techSupply]);
        }

        $handTools = [
            ['name' => 'Hammer Claw Heavy Duty',   'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-HMR-05-294-1',   'date_acquired' => '2021-09-27', 'cost' => 550.00,  'status' => 'Functional'],
            ['name' => 'Hammer Claw Heavy Duty',   'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-HMR-05-294-2',   'date_acquired' => '2021-09-27', 'cost' => 550.00,  'status' => 'Functional'],
            ['name' => 'Chisel (set)',             'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-CHSL-05-294-1',  'date_acquired' => '2021-09-27', 'cost' => 895.00,  'status' => 'Functional'],
            ['name' => 'Screwdriver (set)',        'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-SCRWD-05-294-1', 'date_acquired' => '2021-09-27', 'cost' => 898.00,  'status' => 'Functional'],
            ['name' => 'Screwdriver (set)',        'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-SCRWD-05-294-2', 'date_acquired' => '2021-09-27', 'cost' => 898.00,  'status' => 'Functional'],
            ['name' => 'Handsaw',                  'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-HSAW-05-294-3',  'date_acquired' => '2021-09-27', 'cost' => 585.00,  'status' => 'Functional'],
            ['name' => 'Hacksaw',                  'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-HSAW-05-294-1',  'date_acquired' => '2021-09-27', 'cost' => 650.00,  'status' => 'Functional'],
            ['name' => 'Hacksaw',                  'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-HSAW-05-294-2',  'date_acquired' => '2021-09-27', 'cost' => 650.00,  'status' => 'Functional'],
            ['name' => 'Coping Saw',               'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-CSAW-05-294-1',  'date_acquired' => '2021-09-27', 'cost' => 895.00,  'status' => 'Functional'],
            ['name' => 'Coping Saw',               'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-CSAW-05-294-2',  'date_acquired' => '2021-09-27', 'cost' => 895.00,  'status' => 'Functional'],
            ['name' => 'Jack Plane',               'brand' => 'Heavy Duty STANLEY', 'property_no' => 'ICS-09-27-21-OPE-JACK-05-294-1',  'date_acquired' => '2021-09-27', 'cost' => 6300.00, 'status' => 'Functional'],
        ];

        foreach ($handTools as $item) {
            Equipment::create($item + ['supplier_id' => $genMerch]);
        }
    }
}
