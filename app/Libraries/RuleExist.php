<?php

namespace App\Libraries;

class RuleExist
{
    private function validateMatakuliah($kode_matakuliah) {
    // Check if parameter is empty first
    if (empty($kode_matakuliah)) {
        return false;
    }
    return $this->mataKuliahModel->find($kode_matakuliah) !== null;
    }

    private function validateTimeFormat($time) {
        // Check if parameter is empty first
        if (empty($time)) {
            return false;
        }
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
    }

    private function validateTimeSequence($start, $end) {
        // Check if parameters are empty first
        if (empty($start) || empty($end)) {
            return false;
        }
        return strtotime($end) > strtotime($start);
    }
}