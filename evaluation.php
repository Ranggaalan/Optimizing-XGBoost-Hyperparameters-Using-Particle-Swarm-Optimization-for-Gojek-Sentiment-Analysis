<?php

class Evaluation {

    public static function confusionMatrix($yTrue, $yPred) {

        $tp = $tn = $fp = $fn = 0;

        for ($i = 0; $i < count($yTrue); $i++) {

            if ($yTrue[$i] == 1 && $yPred[$i] == 1) $tp++;
            if ($yTrue[$i] == 0 && $yPred[$i] == 0) $tn++;
            if ($yTrue[$i] == 0 && $yPred[$i] == 1) $fp++;
            if ($yTrue[$i] == 1 && $yPred[$i] == 0) $fn++;
        }

        return [$tp, $tn, $fp, $fn];
    }

    public static function f1Score($tp, $fp, $fn) {

        $precision = $tp / ($tp + $fp + 1e-9);
        $recall = $tp / ($tp + $fn + 1e-9);

        return 2 * ($precision * $recall) / ($precision + $recall + 1e-9);
    }
}
