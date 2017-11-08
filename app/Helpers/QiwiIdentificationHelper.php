<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 26.10.17
 * Time: 10:49
 */

namespace App\Helpers;


use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class QiwiIdentificationHelper {
    public static function getIdentification($login) {
        $qiwiControl = QiwiGeneralHelper::getQiwiControlObject($login);
        $identification = $qiwiControl->getQIWIWalletOwnerData();
//        $identification = false;
        if ($identification == false) throw new ResourceNotFoundException("Identification response not found");

        return $identification;
    }

    public static function updateIdentification($data) {
        $qiwiControl = QiwiGeneralHelper::getQiwiControlObject($data->login);
        //        $qiwiControl->login();

        $identificationUpdateResult = $qiwiControl->setQIWIWalletOwnerData(
                $data->lastName,
                $data->firstName,
                $data->middleName,
                $data->birthDate,
                $data->passport,
                $data->snils,
                $data->inn,
                $data->oms
        );

        dd(['update result' => $identificationUpdateResult]);

        return $identificationUpdateResult;
    }
}