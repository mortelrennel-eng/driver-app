<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ["plate_number"=>"AAK 9196","motor_no"=>"2NZ7307868","chassis_no"=>"NCP151-2031009"],
            ["plate_number"=>"AAQ 1743","motor_no"=>"2NZ7160776","chassis_no"=>"NCP151-2022506"],
            ["plate_number"=>"ABL 6901","motor_no"=>"2NZ7400896","chassis_no"=>"NCP151-2037524"],
            ["plate_number"=>"AEA 9630","motor_no"=>"2NZ7301579","chassis_no"=>"NCP151-2030436"],
            ["plate_number"=>"ALA 3699","motor_no"=>"2NZ7384223","chassis_no"=>"NCP151-2036531"],
            ["plate_number"=>"NAD 1140","motor_no"=>"1NRX093367","chassis_no"=>"PA1B19F36G4016559"],
            ["plate_number"=>"EAD 7438","motor_no"=>"1NRX511105","chassis_no"=>"PA1B13F30K4102617"],
            ["plate_number"=>"NAM 1610","motor_no"=>"1NRX265877","chassis_no"=>"PA1B19F33J4055018"],
            ["plate_number"=>"NDA 8102","motor_no"=>"1NRX399793","chassis_no"=>"PA1B13F30J4076793"],
            ["plate_number"=>"VFL 543","motor_no"=>"2NZ6564244","chassis_no"=>"NCP92-964857"],
            ["plate_number"=>"CAX 5430","motor_no"=>"1NRX765584","chassis_no"=>"PA1B18F37N4171824"],
            ["plate_number"=>"ABF 7471","motor_no"=>"2NZ7470861","chassis_no"=>"NCP151-2042785"],
            ["plate_number"=>"ABG 7479","motor_no"=>"2NZ7494105","chassis_no"=>"NCP151-2043398"],
            ["plate_number"=>"ABL 1667","motor_no"=>"2NZ7542383","chassis_no"=>"NCP151-2046832"],
            ["plate_number"=>"ABP 7643","motor_no"=>"2NZ7541411","chassis_no"=>"NCP151-2046789"],
            ["plate_number"=>"AOA 8917","motor_no"=>"2NZ7263141","chassis_no"=>"NCP151-2028527"],
            ["plate_number"=>"CAT 6073","motor_no"=>"1NRX519089","chassis_no"=>"PA1B18F37K4105320"],
            ["plate_number"=>"CAV 2607","motor_no"=>"1NRX573855","chassis_no"=>"PA1B18F3XL4116880"],
            ["plate_number"=>"CAV 6803","motor_no"=>"1NRX591797","chassis_no"=>"PA1B18F34L4123081"],
            ["plate_number"=>"CAV 9662","motor_no"=>"1NRX622805","chassis_no"=>"PA1B18F33L4126120"],
            ["plate_number"=>"CAV 9716","motor_no"=>"1NRX622596","chassis_no"=>"PA1B18F33L4125985"],
            ["plate_number"=>"CBM 1979","motor_no"=>"1NRX665295","chassis_no"=>"PA1B18F3XM4139156"],
            ["plate_number"=>"DAD 7555","motor_no"=>"1NRX128495","chassis_no"=>"PA1B19F32H4024496"],
            ["plate_number"=>"DAJ 7468","motor_no"=>"1NRX364595","chassis_no"=>"PA1B13F35J4069838"],
            ["plate_number"=>"DAT 1367","motor_no"=>"1NRX586443","chassis_no"=>"PA1B18F37L4121129"],
            ["plate_number"=>"DAU 9027","motor_no"=>"1NRX669745","chassis_no"=>"PA1B18F39M4140346"],
            ["plate_number"=>"DAZ 9769","motor_no"=>"1NRX539051","chassis_no"=>"PA1B18F35L4109741"],
            ["plate_number"=>"DBA 1887","motor_no"=>"1NRX544017","chassis_no"=>"PA1B118F30L4110974"],
            ["plate_number"=>"DBA 2302","motor_no"=>"1NRX530110","chassis_no"=>"PA1B18F38K4108095"],
            ["plate_number"=>"DBA 5420","motor_no"=>"1NRX554443","chassis_no"=>"PA1B18F3XL4112067"],
            ["plate_number"=>"DCQ 1551","motor_no"=>"1NRX049858","chassis_no"=>"PA1B19F37G4007336"],
            ["plate_number"=>"EAE 1247","motor_no"=>"1NRX570523","chassis_no"=>"PA1B18F35L4115295"],
            ["plate_number"=>"EAE 1919","motor_no"=>"1NRX684775","chassis_no"=>"PA1B18F354143793"],
            ["plate_number"=>"EAE 4949","motor_no"=>"1NRX728802","chassis_no"=>"PA1B18F33M4156266"],
            ["plate_number"=>"EAE 5883","motor_no"=>"1NRX735643","chassis_no"=>"PA1B18F3XM4159021"],
            ["plate_number"=>"EAF 6347","motor_no"=>"1NRX587947","chassis_no"=>"PA1B18F34L4121976"],
            ["plate_number"=>"EAF 7245","motor_no"=>"1NRX592060","chassis_no"=>"PA1B18F34L4123212"],
            ["plate_number"=>"NAC 4989","motor_no"=>"1NRX072072","chassis_no"=>"PA1B19F3XG4012319"],
            ["plate_number"=>"NAE 7193","motor_no"=>"1NRX118001","chassis_no"=>"PA1B19F35H4021382"],
            ["plate_number"=>"NBW 7071","motor_no"=>"2NZ7666502","chassis_no"=>"NCP151-2055742"],
            ["plate_number"=>"NBX 4348","motor_no"=>"1NRX136597","chassis_no"=>"PA1B19F31H4026529"],
            ["plate_number"=>"NCN 8583","motor_no"=>"1NRX142517","chassis_no"=>"PA1B119F30H4027929"],
            ["plate_number"=>"NCW 5011","motor_no"=>"1NRX288337","chassis_no"=>"PA1B19F31J060654"],
            ["plate_number"=>"NDA 5429","motor_no"=>"1NRX382535","chassis_no"=>"PA1B13F37J4074295"],
            ["plate_number"=>"NDA 8106","motor_no"=>"1NRX400695","chassis_no"=>"PA1B13F38J4076895"],
            ["plate_number"=>"NDG 7105","motor_no"=>"1NRX074746","chassis_no"=>"PA1B19F32G4012928"],
            ["plate_number"=>"NDI 2585","motor_no"=>"1NRX428966","chassis_no"=>"PA1B13F37K4083631"],
            ["plate_number"=>"NEA 1292","motor_no"=>"1NRX399472","chassis_no"=>"PA1B13F38J4076640"],
            ["plate_number"=>"NEF 4940","motor_no"=>"1NRX507225","chassis_no"=>"PA1B13F31K4102013"],
            ["plate_number"=>"NEI 4883","motor_no"=>"1NRX428108","chassis_no"=>"PA1B119F33K4083254"],
            ["plate_number"=>"NEN 2955","motor_no"=>"1NRX479141","chassis_no"=>"PA1B13F39K4095280"],
            ["plate_number"=>"NEN 2957","motor_no"=>"1NRX478775","chassis_no"=>"PA1B13F37K4095102"],
            ["plate_number"=>"NEP 2440","motor_no"=>"1NRX662804","chassis_no"=>"PA1B18F32M4138437"],
            ["plate_number"=>"NEP 9750","motor_no"=>"1NRX670488","chassis_no"=>"PA1B18F33M4140536"],
            ["plate_number"=>"NEU 5546","motor_no"=>"1NRX494346","chassis_no"=>"PA1B13F39K4098339"],
            ["plate_number"=>"NEV 5065","motor_no"=>"1NRX728865","chassis_no"=>"PA1B18F35M4156270"],
            ["plate_number"=>"NEW 3821","motor_no"=>"1NRX699044","chassis_no"=>"PA1B18F32M4147994"],
            ["plate_number"=>"NEW 6279","motor_no"=>"1NRX711080","chassis_no"=>"PA1B18F33M4150502"],
            ["plate_number"=>"NFH 3664","motor_no"=>"1NRX758930","chassis_no"=>"PA1B18F35N4169456"],
            ["plate_number"=>"NFZ 8295","motor_no"=>"1NRX563284","chassis_no"=>"PA1B18F33L4114131"],
            ["plate_number"=>"NGA 5044","motor_no"=>"1NRX513727","chassis_no"=>"PA1B13F32K4103414"],
            ["plate_number"=>"NGA 7736","motor_no"=>"1NRX585027","chassis_no"=>"PA1B18F33L4120575"],
            ["plate_number"=>"NGB 2854","motor_no"=>"1NRX593170","chassis_no"=>"PA1B18F36L4123549"],
            ["plate_number"=>"NGB 6033","motor_no"=>"1NRX617160","chassis_no"=>"PA1B18F3XL4124719"],
            ["plate_number"=>"NGF 1484","motor_no"=>"1NRX505510","chassis_no"=>"PA134K4101664"],
            ["plate_number"=>"NGO 2629","motor_no"=>"1NRX587826","chassis_no"=>"PA1B18F37L4121826"],
            ["plate_number"=>"VAA 9864","motor_no"=>"1NRX676394","chassis_no"=>"PA1B18F39M4141920"],
            ["plate_number"=>"NAN 1349","motor_no"=>"1NRX560364","chassis_no"=>"PA1B18F35L4113725"],
            ["plate_number"=>"ABP 2705","motor_no"=>"2NZ7557953","chassis_no"=>"NCP151-2048091"]
        ];

        foreach ($data as $row) {
            \Illuminate\Support\Facades\DB::table('units')
                ->where('plate_number', $row['plate_number'])
                ->update([
                    'motor_no' => $row['motor_no'],
                    'chassis_no' => $row['chassis_no'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
