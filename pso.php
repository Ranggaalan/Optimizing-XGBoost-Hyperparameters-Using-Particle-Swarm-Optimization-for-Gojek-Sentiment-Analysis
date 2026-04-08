<?php

class PSO {

    public static function optimize($X, $y) {

        $particleCount = 5;

        $bestScore = -INF;
        $bestParams = [0.3, 30];

        for ($p = 0; $p < $particleCount; $p++) {

            $learningRate = rand(1, 5) / 10; // 0.1 - 0.5
            $nEstimators = rand(10, 50);

            $model = new SimpleXGBoost($learningRate, $nEstimators);
            $model->train($X, $y);

            $correct = 0;
            foreach ($X as $i => $features) {
                if ($model->predict($features) == $y[$i]) {
                    $correct++;
                }
            }

            $accuracy = $correct / count($X);

            if ($accuracy > $bestScore) {
                $bestScore = $accuracy;
                $bestParams = [$learningRate, $nEstimators];
            }
        }

        return $bestParams;
    }
}
