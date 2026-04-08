<?php

class DataSplit {

    public static function split($X, $y, $ratio = 0.8) {

        $data = [];
        for ($i = 0; $i < count($X); $i++) {
            $data[] = [$X[$i], $y[$i]];
        }

        shuffle($data);

        $splitIndex = floor(count($data) * $ratio);

        $train = array_slice($data, 0, $splitIndex);
        $test = array_slice($data, $splitIndex);

        return [$train, $test];
    }
}
