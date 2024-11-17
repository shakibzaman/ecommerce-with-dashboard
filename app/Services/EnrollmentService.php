<?php


namespace App\Services;


use App\Models\LifetimePackage;
use App\Models\MonthlyPackage;

class EnrollmentService
{
    public function commissionCalculator($user,$package)
    {
        $lifetimePackage = LifetimePackage::find($package);

        $log = new LogService();
        $log->membershipLog($user->id,$package,$lifetimePackage->price);

        $firstLabelLeader = $user->leader;
        if ($firstLabelLeader){
            if (!$firstLabelLeader->lifetimePackage){
                return;
            }
            $secondLabelLeader = $firstLabelLeader->leader;
            $commissionPercentage = $firstLabelLeader->lifetimePackage->percentage_label_1;
            $firstLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);
            $firstLabelLeader->update([
                'balance' => $firstLabelLeader->balance + $firstLabelLeaderCommission
            ]);
        }

        if (isset($secondLabelLeader)){
            if (!$secondLabelLeader->lifetimePackage){
                return;
            }
            $thirdLabelLeader = $secondLabelLeader->leader;
            $commissionPercentage = $secondLabelLeader->lifetimePackage->percentage_label_2;
            $secondLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);

            $secondLabelLeader->update([
                'balance' => $secondLabelLeader->balance + $secondLabelLeaderCommission
            ]);
        }

        if (isset($thirdLabelLeader)){
            if (!$thirdLabelLeader->lifetimePackage){
                return;
            }
            $commissionPercentage = $thirdLabelLeader->lifetimePackage->percentage_label_3;
            $thirdLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);

            $thirdLabelLeader->update([
                'balance' => $thirdLabelLeader->balance + $thirdLabelLeaderCommission
            ]);
        }
        return true;
    }

    private function percentageCalculation($amount, $percent)
    {
        $total = ($amount / 100) * $percent;
        return number_format($total, 2);
    }

    public function monthlyCommissionCalculator($user,$package)
    {
        $lifetimePackage = MonthlyPackage::find($package);
        $log = new LogService();
        $log->subscriptionLog($user->id,$package,$lifetimePackage->price);
        $firstLabelLeader = $user->leader;
        if ($firstLabelLeader){
            if (!$firstLabelLeader->monthlyPackage){
                return;
            }
            if ($firstLabelLeader->monthly_package_status == 'inactive'){
                return;
            }
            $secondLabelLeader = $firstLabelLeader->leader;
            $commissionPercentage = $firstLabelLeader->monthlyPackage->percentage_label_1;
            $firstLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);
            $firstLabelLeader->update([
                'balance' => $firstLabelLeader->balance + $firstLabelLeaderCommission
            ]);
        }

        if (isset($secondLabelLeader)){

            if ($secondLabelLeader->monthly_package_status == 'inactive'){
                return;
            }

            $thirdLabelLeader = $secondLabelLeader->leader;
            $commissionPercentage = $secondLabelLeader->monthlyPackage->percentage_label_2;
            $secondLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);

            $secondLabelLeader->update([
                'balance' => $secondLabelLeader->balance + $secondLabelLeaderCommission
            ]);
        }

        if (isset($thirdLabelLeader)){

            if ($thirdLabelLeader->monthly_package_status == 'inactive'){
                return;
            }
            $commissionPercentage = $thirdLabelLeader->monthlyPackage->percentage_label_3;
            $thirdLabelLeaderCommission = $this->percentageCalculation($lifetimePackage->price, $commissionPercentage);

            $thirdLabelLeader->update([
                'balance' => $thirdLabelLeader->balance + $thirdLabelLeaderCommission
            ]);
        }
        return true;
    }
}
