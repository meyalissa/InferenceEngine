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
    $premises = explode("\n", strtolower(trim($_POST["premises"])));
    $conclusion = strtolower(trim($_POST["conclusion"]));

    function add_to_symbols($stmt, &$symbols, &$reverse, &$letter) {
        $clean = strtolower(trim($stmt));
        if (str_starts_with($clean, "not ")) {
            $clean = trim(substr($clean, 4));
        }
        if (!isset($symbols[$clean])) {
            $symbols[$clean] = $letter;
            $reverse[$letter] = $clean;
            $letter++;
        }
    }

    // Step 1: Assign letters (p, q, r...) to each unique statement
    $all_statements = array_merge($premises, [$conclusion]);
    $symbols = [];
    $reverse = [];
    $letter = 'p';

    foreach ($premises as $line) {
        $line = trim(strtolower($line));
        if (str_starts_with($line, "if") && str_contains($line, "then")) {
            $parts = explode("then", substr($line, 3), 2);
            $cond = trim($parts[0]);
            $res = trim($parts[1]);
            add_to_symbols($cond, $symbols, $reverse, $letter);
            add_to_symbols($res, $symbols, $reverse, $letter);
        } else {
            add_to_symbols($line, $symbols, $reverse, $letter);
        }
    }
    add_to_symbols($conclusion, $symbols, $reverse, $letter);

    // Step 2: Convert premises to logic
    $known = [];         // stores p, q, ¬p, etc.
    $implications = [];  // stores "p -> q"
    $steps = [];

    foreach ($premises as $line) {
        $line = trim($line);
        if (str_starts_with($line, "if") && str_contains($line, "then")) {
            // Example: if a then b → "a -> b"
            $parts = explode("then", substr($line, 3), 2);
            $cond = trim($parts[0]);
            $result = trim($parts[1]);
            $implications[] = $symbols[$cond] . " -> " . $symbols[$result];

        } elseif (str_starts_with($line, "not ")) {
            $stmt = trim(substr($line, 4));
            $known[] = "¬" . $symbols[$stmt];
        } else {
            $known[] = $symbols[$line];
        }
    }

    // Step 3: Prepare the conclusion logic
    if (str_starts_with($conclusion, "not ")) {
        $stmt = trim(substr($conclusion, 4));
        $conclusion_symbol = "¬" . $symbols[$stmt];
    } elseif (str_starts_with($conclusion, "if") && str_contains($conclusion, "then")) {
        $parts = explode("then", substr($conclusion, 3), 2);
        $cond = trim($parts[0]);
        $res = trim($parts[1]);
        $conclusion_symbol = $symbols[$cond] . " -> " . $symbols[$res];
    } else {
        $conclusion_symbol = $symbols[$conclusion];
    }


    // Step 4: Apply Modus Ponens
    $inferred = $known;
    $changed = true;

    while ($changed) {
        $changed = false;
        foreach ($implications as $rule) {
            list($a, $b) = explode("->", $rule);
            $a = trim($a); $b = trim($b);

            // Modus Ponens: a and a → b → infer b
            if (in_array($a, $inferred) && !in_array($b, $inferred)) {
                $inferred[] = $b;
                $steps[] = "Modus Ponens: from $a and ($a → $b), infer $b";
                $changed = true;
            }

            // Modus Tollens: ¬b and a → b → infer ¬a
            else if (in_array("¬$b", $inferred) && !in_array("¬$a", $inferred)) {
                $inferred[] = "¬$a";
                $steps[] = "Modus Tollens: from ¬$b and ($a → $b), infer ¬$a";
                $changed = true;
            }

            
        }
            //Hypothetical Syllogism: a → b, b → c ⟹ a → c
        foreach ($implications as $rule1) {
            list($a1, $b1) = array_map('trim', explode("->", $rule1));
            foreach ($implications as $rule2) {
                list($a2, $b2) = array_map('trim', explode("->", $rule2));
                if ($b1 === $a2) {
                    $newRule = "$a1 -> $b2";
                    // Avoid duplicate
                    if (!in_array($newRule, $implications)) {
                        $implications[] = $newRule;
                        $steps[] = "Hypothetical Syllogism: from ($a1 → $b1) and ($a2 → $b2), infer ($newRule)";
                        $changed = true;
                    }
                }
            }
        }
    }

   // Step 6: Display Premises as p, q, r...
    echo "<h3>Premises:</h3>";
    $premise_count = 'p';
    foreach ($premises as $line) {
        $line = trim($line);
        if (str_starts_with($line, "if") && str_contains($line, "then")) {
            // Extract the conditional statement: "If p then q"
            $parts = explode("then", $line, 2);
            $cond = trim($parts[0]);
            $res = trim($parts[1]);
            echo "<p>" . $premise_count . ": " . ucfirst($cond) . " → " . ucfirst($res) . "</p>";
        } else {
            echo "<p>" . $premise_count . ": " . ucfirst($line) . "</p>";
        }
        $premise_count++;
    }

    // Step 5: Show Result
    echo "<h3>Steps:</h3>";
    if ($steps) {
        echo "<ul>";
        foreach ($steps as $step) {
            echo "<li>$step</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No inference steps applied.</p>";
    }
    

    echo "<h3>Conclusion:</h3>";
    if (in_array($conclusion_symbol, $inferred) || in_array($conclusion_symbol, $implications)) {
      
        echo "<p style='color:green;'>Conclusion is VALID.</p>";
    } else {
        
        echo "<p style='color:red;'>Conclusion is NOT valid.</p>";
    }

}
?>
</body>
</html>
