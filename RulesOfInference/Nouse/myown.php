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

    // Display the content of $premises and $conclusion
    echo "Premises:<br>";
    print_r($premises); // Display $premises array
    echo "<br><br>Conclusion:<br>";
    echo $conclusion; // Display $conclusion string

 function add_to_symbols($stmt, &$symbols, &$reverse, &$letter, &$propositions) {
    $clean = strtolower(trim($stmt));
    // Handle 'not' case
    if (str_starts_with($clean, "not ")) {
        $clean = trim(substr($clean, 4)); // Remove the "not " part
    }

    // Only assign the letter if the statement isn't already in symbols
    if (!isset($symbols[$clean])) {
        $symbols[$clean] = $letter; // Assign letter to the cleaned statement.
        $reverse[$letter] = $clean; // Reverse lookup for letter to statement.
        $letter++; // Increment the letter for the next statement.

        // Assign truth value to the proposition
        if (isset($propositions[$clean])) {
            $propositions[$clean] = true; // default value (can be adjusted later)
        }
    }
}

function check_validity($propositions, $conclusion) {
    // If the conclusion is in the propositions, return its value
    if (isset($propositions[$conclusion])) {
        return $propositions[$conclusion] ? "Valid" : "Invalid";
    }
    return "Invalid";
}

// Example input statements
$premises = [
    "Ali works hard",
    "If Ali works hard then he is a dull boy",
    "If Ali works hard or he is a lazy boy, then he is not a dull boy",
    "Ali works hard and he is a dull boy",
    "Not Ali works hard"
];

$propositions = array(
    "p" => true,       // 0
    "q" => true,       // 1
    "r" => true,       // 2
    "~p" => false,     // 3
    "~q" => false,     // 4
    "~r" => false,     // 5
    "p->q" => true,    // 6
    "p->r" => false,   // 7
    "q->p" => false,   // 8
    "q->r" => false,   // 9
    "r->p" => true,    // 10
    "r->q" => true,    // 11
);

// Initialize necessary arrays and the first letter 'p'
$symbols = [];
$reverse = [];
$letter = 'p'; // Start with 'p'

// Loop through each premise
foreach ($premises as $line) {
    $line = trim(strtolower($line)); // Clean up the statement

    // Case for handling "If...Then" (conditionals)
    if (str_starts_with($line, "if") && str_contains($line, "then")) {
        // Split the statement into the condition and result
        $parts = explode("then", substr($line, 3), 2); // Split into condition and result
        $cond = trim($parts[0]);
        $res = trim($parts[1]);

        // Assign letters to the condition and result
        add_to_symbols($cond, $symbols, $reverse, $letter, $propositions);
        add_to_symbols($res, $symbols, $reverse, $letter, $propositions);

        // Check truth value from $propositions and print it
        $cond_val = $propositions[$cond] ?? false;
        $res_val = $propositions[$res] ?? false;

        echo "p: $cond -> $cond_val\n";
        echo "q: $res -> $res_val\n";

        // Handle the implication (p -> q)
        $implication = ($cond_val && $res_val) ? true : false;
        $propositions["$cond->$res"] = $implication;  // Store the implication truth value
        echo "$cond->$res = " . ($implication ? "true" : "false") . "\n";
    }

    // Case for handling "Not" (negation)
    elseif (str_starts_with($line, "not ")) {
        $stmt = substr($line, 4); // Remove the "not " part
        add_to_symbols($stmt, $symbols, $reverse, $letter, $propositions);

        // Negation will reverse the truth value
        $stmt_val = $propositions[$stmt] ?? false;
        $neg_val = !$stmt_val;
        $propositions["~$stmt"] = $neg_val; // Store the negation truth value
        echo "p: not $stmt -> " . ($neg_val ? "true" : "false") . "\n";
    }

    // Case for handling "or" (disjunction)
    elseif (str_contains($line, "or")) {
        $parts = explode("or", $line, 2); // Split into the two parts
        $stmt1 = trim($parts[0]);
        $stmt2 = trim($parts[1]);

        // Assign letters to both parts of the disjunction
        add_to_symbols($stmt1, $symbols, $reverse, $letter, $propositions);
        add_to_symbols($stmt2, $symbols, $reverse, $letter, $propositions);

        // Handle the disjunction (p ∨ q)
        $stmt1_val = $propositions[$stmt1] ?? false;
        $stmt2_val = $propositions[$stmt2] ?? false;
        $or_val = $stmt1_val || $stmt2_val; // OR operation
        $propositions["$stmt1 ∨ $stmt2"] = $or_val; // Store the disjunction truth value
        echo "p: $stmt1 or $stmt2 -> " . ($or_val ? "true" : "false") . "\n";
    }

    // Case for handling "and" (conjunction)
    elseif (str_contains($line, "and")) {
        $parts = explode("and", $line, 2); // Split into the two parts
        $stmt1 = trim($parts[0]);
        $stmt2 = trim($parts[1]);

        // Assign letters to both parts of the conjunction
        add_to_symbols($stmt1, $symbols, $reverse, $letter, $propositions);
        add_to_symbols($stmt2, $symbols, $reverse, $letter, $propositions);

        // Handle the conjunction (p ∧ q)
        $stmt1_val = $propositions[$stmt1] ?? false;
        $stmt2_val = $propositions[$stmt2] ?? false;
        $and_val = $stmt1_val && $stmt2_val; // AND operation
        $propositions["$stmt1 ∧ $stmt2"] = $and_val; // Store the conjunction truth value
        echo "p: $stmt1 and $stmt2 -> " . ($and_val ? "true" : "false") . "\n";
    }

    // General case (if no special operators are present)
    else {
        add_to_symbols($line, $symbols, $reverse, $letter, $propositions);
        echo "p: $line -> " . ($propositions[$line] ?? false ? "true" : "false") . "\n";
    }
}

// Check validity of the conclusion
$conclusion = "p->q"; // Example conclusion (can be changed based on the case)
echo "Conclusion: " . check_validity($propositions, $conclusion) . "\n";

// Debugging: Display the content of the variables
echo "<br><br>Symbols Array:<br>";
print_r($symbols); // Display the $symbols array

echo "<br><br>Reverse Array:<br>";
print_r($reverse); // Display the $reverse array

echo "<br><br>Propositions Array:<br>";
print_r($propositions); // Display the $propositions array

}

?>
</body>
</html>
