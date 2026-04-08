<?php

class TFIDF {

    public static function buildVocabulary($documents, $max_features = 3000) {

        $freq = [];

        foreach ($documents as $doc) {
            $words = explode(" ", $doc);

            foreach ($words as $w) {
                if ($w == "") continue;

                if (!isset($freq[$w])) {
                    $freq[$w] = 0;
                }
                $freq[$w]++;
            }
        }

        arsort($freq);
        return array_slice(array_keys($freq), 0, $max_features);
    }

    public static function computeIDF($documents, $vocab) {

        $idf = [];
        $totalDocs = count($documents);

        foreach ($vocab as $term) {

            $docCount = 0;

            foreach ($documents as $doc) {
                $words = explode(" ", $doc);
                if (in_array($term, $words)) {
                    $docCount++;
                }
            }

            $idf[] = log(($totalDocs + 1) / ($docCount + 1));
        }

        return $idf;
    }

    public static function computeTFIDFTrain($documents) {

        echo "Membangun vocabulary dari TRAIN...\n";

        $vocab = self::buildVocabulary($documents);
        $idf   = self::computeIDF($documents, $vocab);

        $matrix = [];

        foreach ($documents as $doc) {

            $words = explode(" ", $doc);
            $wordCounts = array_count_values($words);

            $tfidf = [];

            foreach ($vocab as $j => $term) {

                $tf = isset($wordCounts[$term])
                    ? $wordCounts[$term] / count($words)
                    : 0;

                $tfidf[] = $tf * $idf[$j];
            }

            $matrix[] = $tfidf;
        }

        return [$matrix, $vocab, $idf];
    }

    public static function computeTFIDFTest($documents, $vocab, $idf) {

        $matrix = [];

        foreach ($documents as $doc) {

            $words = explode(" ", $doc);
            $wordCounts = array_count_values($words);

            $tfidf = [];

            foreach ($vocab as $j => $term) {

                $tf = isset($wordCounts[$term])
                    ? $wordCounts[$term] / count($words)
                    : 0;

                $tfidf[] = $tf * $idf[$j];
            }

            $matrix[] = $tfidf;
        }

        return $matrix;
    }
}
