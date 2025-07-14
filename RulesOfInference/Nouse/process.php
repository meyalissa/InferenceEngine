<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Step 1: Get the number of premises
    $numPremises = $_POST['numPremises'];
    
    // Step 2: Get the premises
    $premises = [];
    for ($i = 1; $i <= $numPremises; $i++) {
        $premises[] = $_POST['premise' . $i];
    }

    // Step 3: Get the selected rule of inference
    $rule = $_POST['rule'];

    // Step 4: Get the statements for p, q, r, and conclusion
    $p = $_POST['p'];
    $q = $_POST['q'];
    $r = isset($_POST['r']) ? $_POST['r'] : null;
    $conclusion = $_POST['conclusion'];

    // Initialize the propositions array
    $propositions = array();
    $propositions["p"] = true; //0
    $propositions["q"] = true; //1
    $propositions["r"] = true; //2
    $propositions["~p"] = false; //3
    $propositions["~q"] = false; //4
    $propositions["~r"] = false; //5
    $propositions["p->q"] = true; //6
    $propositions["p->r"] = false;  //7
    $propositions["q->p"] = false; //8
    $propositions["q->r"] = false;  //9  
    $propositions["r->p"] = true; //10
    $propositions["r->q"] = true;  //11

    //Apply the selected rule of inference
    $result = "";

    switch ($rule) {
        case 1: // Modus Ponens (If p → q and p, then q)
            if ($propositions["p->q"] && $propositions["p"]) {
                $propositions["q"] = true;  // Therefore, q must be true
                $result = "Conclusion (Modus Ponens): " . $conclusion;
            } else {
                $result = "Modus Ponens failed. Premises don't match." ;
            }
            break;
        case 2: // Modus Tollens (If p → q and ¬q, then ¬p)
            if ($propositions["p->q"] && $propositions["~q"]) {
                $propositions["~p"] = true;  // Therefore, ~p must be true
                $result = "Conclusion (Modus Tollens): " . $conclusion;
            } else {
                $result = "Modus Tollens failed. Premises don't match.";
            }
            break;
        case 3: // Hypothetical Syllogism (If p → q and q → r, then p → r)
            if ($propositions["p->q"] && $propositions["q->r"]) {
                $result = "Conclusion (Hypothetical Syllogism): " . $conclusion;
            } else {
                $result = "Hypothetical Syllogism failed. Premises don't match.";
            }
            break;
        // Add cases for other rules as needed
        default:
            $result = "Invalid rule selected.";
    }

    // Show the result
    header("Location: ../index.php?result=" . urlencode($result));
    exit();
}
?>
