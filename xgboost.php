<?php

class SimpleXGBoost {

    private $nClasses = 3;
    private $learningRate;
    private $nEstimators;
    private $models = [];

    public function __construct($learningRate = 0.3, $nEstimators = 30) {
        $this->learningRate = $learningRate;
        $this->nEstimators = $nEstimators;
    }

    public function train($X, $y) {

        $nSamples = count($X);
        $nFeatures = count($X[0]);

        // initialize raw prediction scores
        $pred = array_fill(0, $nSamples, array_fill(0, $this->nClasses, 0));

        for ($m = 0; $m < $this->nEstimators; $m++) {

            $trees = [];

            for ($class = 0; $class < $this->nClasses; $class++) {

                $gradients = [];

                for ($i = 0; $i < $nSamples; $i++) {

                    $probs = $this->softmax($pred[$i]);
                    $yTrue = ($y[$i] == $class) ? 1 : 0;

                    // gradient logloss
                    $gradients[$i] = $yTrue - $probs[$class];
                }

                // weak learner (linear stump)
                $weights = array_fill(0, $nFeatures, 0);

                foreach ($X as $i => $features) {
                    foreach ($features as $j => $value) {
                        $weights[$j] += $this->learningRate * $gradients[$i] * $value;
                    }
                }

                $trees[$class] = $weights;
            }

            // update predictions
            foreach ($X as $i => $features) {
                for ($class = 0; $class < $this->nClasses; $class++) {
                    $pred[$i][$class] += $this->dot($features, $trees[$class]);
                }
            }

            $this->models[] = $trees;
        }
    }

    private function dot($a, $b) {
        $sum = 0;
        foreach ($a as $i => $val) {
            $sum += $val * $b[$i];
        }
        return $sum;
    }

    private function softmax($scores) {
        $exp = [];
        $sum = 0;

        foreach ($scores as $s) {
            $e = exp($s);
            $exp[] = $e;
            $sum += $e;
        }

        foreach ($exp as $i => $e) {
            $exp[$i] = $e / ($sum + 1e-9);
        }

        return $exp;
    }

    public function predict($features) {

        $scores = array_fill(0, $this->nClasses, 0);

        foreach ($this->models as $trees) {
            for ($class = 0; $class < $this->nClasses; $class++) {
                $scores[$class] += $this->dot($features, $trees[$class]);
            }
        }

        $probs = $this->softmax($scores);

        return array_search(max($probs), $probs);
    }
}
