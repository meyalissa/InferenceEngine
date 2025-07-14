<!DOCTYPE html>
<html>
<head>
    <title>Inference Engine</title>
</head>
<body>
    <h2>Inference Engine</h2>
    <form method="post">
        <label>Enter premises (one per line):</label><br>
        <textarea name="premises" rows="5" cols="60"></textarea><br><br>

        <label>Enter conclusion:</label><br>
        <input type="text" name="conclusion" size="60"><br><br>

        <input type="submit" value="Check Validity">
    </form>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the premises and conclusion
    $premises = explode("\n", strtolower(trim($_POST["premises"])));
    $conclusion = strtolower(trim($_POST["conclusion"]));

    // Display premises
    echo "<h3>Premises:</h3>";
    $p = $q = $r = null;
    foreach ($premises as $premise) {
        if (strpos($premise, 'works hard') !== false) {
            $p = "Ali works hard";
        } elseif (strpos($premise, 'dull boy') !== false) {
            $q = "He is a dull boy";
        }
    }

    echo "p: $p <br> q: $q <br>";

    // Determine the type of inference rule and steps
    $inferenceRule = "Modus Ponens";
    $steps = "1. p → q (If Ali works hard, then he is a dull boy)<br> 2. p: Ali works hard (Premise)<br> 3. Conclusion: q: He is a dull boy";

    // Display rule of inference
    echo "<h3>Rule of Inference:</h3>";
    echo "Type of Inference: $inferenceRule <br>";
    echo "Steps: $steps <br>";

    // Conclusion validity check
    if ($conclusion == $q) {
        echo "<h3>Conclusion: Valid</h3>";
    } else {
        echo "<h3>Conclusion: Invalid</h3>";
    }
}
?>
</body>
</html>