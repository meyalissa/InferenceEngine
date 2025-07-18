<!-- TEAM MEMBERS:
MELISSA SOFIA 
MARSYA QISTINA
FARAH AISYAH
KHAULAH KAREEMA -->

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inference Engine</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Inference Engine</h1>
    <div class="main-container">
        
        <!-- Input Section -->
        <div class="input-section">
            <form method="post">
                <div class="form-group">
                    <label for="premises">Enter Premises :</label> <!--enter statement per line -->
                    <textarea name="premises" class="input" placeholder="Enter your premises here..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="conclusion">Enter Conclusion :</label> <!--enter conclusion -->
                    <input type="text" name="conclusion" class="input" placeholder="Enter your conclusion here..." required />
                </div>

                <div class="form-group">
                    <button type="submit" id="checkBtn" class="btn btn-lg">CHECK VALIDITY</button>
                </div>
            </form>
        </div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the premises and conclusion
    $premises_raw = strtolower(trim($_POST["premises"]));
    $premises = array_map('trim', explode("\n", $premises_raw));
    $conclusion = strtolower(trim($_POST["conclusion"]));

    // Step 1: Dynamically assign variables to unique statements
    $statements = [];
    $vars = [];
    $varNames = range('p', 'z');
    $varIndex = 0;

    // Helper to assign/get variable for a statement
    function getVar($stmt, &$statements, &$vars, &$varNames, &$varIndex) {
        $stmt = trim($stmt);
        if (!isset($statements[$stmt])) {
            $statements[$stmt] = $varNames[$varIndex];
            $vars[$varNames[$varIndex]] = $stmt;
            $varIndex++;
        }
        return $statements[$stmt];
    }

    // Helper to normalize negations for consistent variable assignment
    function normalize_negation($stmt) {
        $stmt = trim($stmt);
        // Handle "does not", "do not", "did not", "is not", "are not", "was not", "were not", "not"
        $patterns = [
            '/^(.*) does not (.*)$/i' => 'not $1 $2',
            '/^(.*) do not (.*)$/i'   => 'not $1 $2',
            '/^(.*) did not (.*)$/i'  => 'not $1 $2',
            '/^(.*) is not (.*)$/i'   => 'not $1 is $2',
            '/^(.*) are not (.*)$/i'  => 'not $1 are $2',
            '/^(.*) was not (.*)$/i'  => 'not $1 was $2',
            '/^(.*) were not (.*)$/i' => 'not $1 were $2',
            '/not (.+)/i'             => 'not $1',
        ];
        foreach ($patterns as $pattern => $replace) {
            if (preg_match($pattern, $stmt, $m)) {
                $stmt = preg_replace($pattern, $replace, $stmt);
                break;
            }
        }
        return trim($stmt);
    }

    // Parse premises for logical forms
    $parsedPremises = [];
    foreach ($premises as $premise) {
        $premise = normalize_negation($premise);
    
        if (preg_match('/if (.+) then (.+)/', $premise, $m)) {
            // Handle "if ... then ..." statements
            $a = getVar(normalize_negation($m[1]), $statements, $vars, $varNames, $varIndex);
            $b = getVar(normalize_negation($m[2]), $statements, $vars, $varNames, $varIndex);
            $parsedPremises[] = "$a → $b";
        } elseif (preg_match('/(.+) or (.+)/', $premise, $m)) {
            // Handle "or" statements with negation detection
            $part1 = trim($m[1]);
            $part2 = trim($m[2]);
            if (preg_match('/^not (.+)/', $part1, $n1)) {
                $a = '¬' . getVar(trim($n1[1]), $statements, $vars, $varNames, $varIndex);
            } else {
                $a = getVar($part1, $statements, $vars, $varNames, $varIndex);
            }
            if (preg_match('/^not (.+)/', $part2, $n2)) {
                $b = '¬' . getVar(trim($n2[1]), $statements, $vars, $varNames, $varIndex);
            } else {
                $b = getVar($part2, $statements, $vars, $varNames, $varIndex);
            }
            $parsedPremises[] = "$a ∨ $b";
        } elseif (preg_match('/(.+) and (.+)/', $premise, $m)) {
            // Handle "and" statements with negation detection
            $part1 = trim($m[1]);
            $part2 = trim($m[2]);
            if (preg_match('/^not (.+)/', $part1, $n1)) {
                $a = '¬' . getVar(trim($n1[1]), $statements, $vars, $varNames, $varIndex);
            } else {
                $a = getVar($part1, $statements, $vars, $varNames, $varIndex);
            }
            if (preg_match('/^not (.+)/', $part2, $n2)) {
                $b = '¬' . getVar(trim($n2[1]), $statements, $vars, $varNames, $varIndex);
            } else {
                $b = getVar($part2, $statements, $vars, $varNames, $varIndex);
            }
            $parsedPremises[] = "$a ∧ $b";
        } elseif (preg_match('/not (.+)/', $premise, $m)) {
            // Handle negation statements
            $base = trim($m[1]);
            $a = getVar($base, $statements, $vars, $varNames, $varIndex); // Use base statement for variable
            $parsedPremises[] = "¬$a";
        } else {
            // Handle simple statements
            $a = getVar($premise, $statements, $vars, $varNames, $varIndex);
            $parsedPremises[] = "$a";
        }
    }

    // Parse conclusion
    $conclusionNorm = normalize_negation($conclusion);
    if (preg_match('/if (.+) then (.+)/', $conclusionNorm, $m)) {
        // Handle "if ... then ..." statements in conclusion
        $a = getVar(normalize_negation($m[1]), $statements, $vars, $varNames, $varIndex);
        $b = getVar(normalize_negation($m[2]), $statements, $vars, $varNames, $varIndex);
        $parsedConclusion = "$a → $b";
    } elseif (preg_match('/(.+) or (.+)/', $conclusionNorm, $m)) {
        // Handle "or" statements in conclusion
        $a = getVar(normalize_negation($m[1]), $statements, $vars, $varNames, $varIndex);
        $b = getVar(normalize_negation($m[2]), $statements, $vars, $varNames, $varIndex);
        $parsedConclusion = "$a ∨ $b";
    } elseif (preg_match('/(.+) and (.+)/', $conclusionNorm, $m)) {
        // Handle "and" statements in conclusion
        $a = getVar(normalize_negation($m[1]), $statements, $vars, $varNames, $varIndex);
        $b = getVar(normalize_negation($m[2]), $statements, $vars, $varNames, $varIndex);
        $parsedConclusion = "$a ∧ $b";
    } elseif (preg_match('/not (.+)/', $conclusionNorm, $m)) {
        // Handle negation statements in conclusion
        $base = trim($m[1]);
        $a = getVar($base, $statements, $vars, $varNames, $varIndex); // Use base statement for variable
        $parsedConclusion = "¬$a";
    } else {
        // Handle simple statements in conclusion
        $a = getVar($conclusionNorm, $statements, $vars, $varNames, $varIndex);
        $parsedConclusion = "$a";
    }

        // Step 2: Display premises with variable mapping
        $premisesOutput = "";
        foreach ($vars as $var => $stmt) {
            $premisesOutput .= "$var: $stmt\n";
        }

        // Step 3: Try to identify the rule of inference and steps
        $inferenceRule = "Unknown";
        $steps = "";
        $valid = false;

        // Modus Ponens: If p → q, p, then q.
        if (
            in_array("$varNames[0] → $varNames[1]", $parsedPremises) &&
            in_array("$varNames[0]", $parsedPremises) &&
            $parsedConclusion == "$varNames[1]"
        ) {
            $inferenceRule = "Modus Ponens";
            $steps = "<br>1. {$varNames[0]} → {$varNames[1]} (If {$vars[$varNames[0]]}, then {$vars[$varNames[1]]}) <br> 2. {$varNames[0]}: {$vars[$varNames[0]]} (Premise)<br>3. Conclusion:<br> {$varNames[1]}: {$vars[$varNames[1]]}";
            $valid = true;
        }
        // Modus Tollens: If p → q, ¬q, then ¬p.
        elseif (
            in_array("$varNames[0] → $varNames[1]", $parsedPremises) &&
            in_array("¬$varNames[1]", $parsedPremises) &&
            $parsedConclusion == "¬$varNames[0]"
        ) {
            $inferenceRule = "Modus Tollens";
            $steps = "<br>1. {$varNames[0]} → {$varNames[1]} (If {$vars[$varNames[0]]}, then {$vars[$varNames[1]]})<br>2. ¬{$varNames[1]}: Not {$vars[$varNames[1]]} (Premise)<br>3. Conclusion:<br> ¬{$varNames[0]}: Not {$vars[$varNames[0]]}";
            $valid = true;
        }
        // Hypothetical Syllogism: If p → q and q → r, then p → r.
        elseif (
            in_array("$varNames[0] → $varNames[1]", $parsedPremises) &&
            in_array("$varNames[1] → $varNames[2]", $parsedPremises) &&
            $parsedConclusion == "$varNames[0] → $varNames[2]"
        ) {
            $inferenceRule = "Hypothetical Syllogism";
            $steps = "<br>1. {$varNames[0]} → {$varNames[1]} (If {$vars[$varNames[0]]}, then {$vars[$varNames[1]]})<br>2. {$varNames[1]} → {$varNames[2]} (If {$vars[$varNames[1]]}, then {$vars[$varNames[2]]})<br>3. Conclusion:<br> {$varNames[0]} → {$varNames[2]} (If {$vars[$varNames[0]]}, then {$vars[$varNames[2]]})";
            $valid = true;
        }
        // Hypothetical Syllogism (Derived): If p, p → q, q → r, then r.
        elseif (
            in_array("$varNames[0]", $parsedPremises) &&
            in_array("$varNames[0] → $varNames[1]", $parsedPremises) &&
            in_array("$varNames[1] → $varNames[2]", $parsedPremises) &&
            $parsedConclusion == "$varNames[2]"
        ) {
            $inferenceRule = "Hypothetical Syllogism (Chain)";
            $steps = "<br>1. {$varNames[0]}: {$vars[$varNames[0]]} (Premise)"
                . "<br>2. {$varNames[0]} → {$varNames[1]} (If {$vars[$varNames[0]]}, then {$vars[$varNames[1]]})"
                . "<br>3. {$varNames[1]} → {$varNames[2]} (If {$vars[$varNames[1]]}, then {$vars[$varNames[2]]})"
                . "<br>4. Therefore, {$varNames[2]}: {$vars[$varNames[2]]}";
            $valid = true;
        }
        // Disjunctive Syllogism: If p ∨ q, ¬p, then q.
        elseif (
            in_array("$varNames[0] ∨ $varNames[1]", $parsedPremises) &&
            in_array("¬$varNames[0]", $parsedPremises) &&
            $parsedConclusion == "$varNames[1]"
        ) {
            $inferenceRule = "Disjunctive Syllogism";
            $steps = "<br>1. {$varNames[0]} ∨ {$varNames[1]} ({$vars[$varNames[0]]} or {$vars[$varNames[1]]})<br>2. ¬{$varNames[0]}: Not {$vars[$varNames[0]]} (Premise)<br>3. Conclusion:<br> {$varNames[1]}: {$vars[$varNames[1]]}";
            $valid = true;
        }
        // Conjunction: If p, q, then p ∧ q.
        elseif (
            in_array("$varNames[0]", $parsedPremises) &&
            in_array("$varNames[1]", $parsedPremises) &&
            $parsedConclusion == "$varNames[0] ∧ $varNames[1]"
        ) {
            $inferenceRule = "Conjunction";
            $steps = "<br>1. {$varNames[0]}: {$vars[$varNames[0]]} (Premise)<br>2. {$varNames[1]}: {$vars[$varNames[1]]} (Premise)<br>3. Conclusion:<br> {$varNames[0]} ∧ {$varNames[1]}: {$vars[$varNames[0]]} and {$vars[$varNames[1]]}";
            $valid = true;
        }
        // Simplification: If p ∧ q, then p.
        elseif (
            in_array("$varNames[0] ∧ $varNames[1]", $parsedPremises) &&
            $parsedConclusion == "$varNames[0]"
        ) {
            $inferenceRule = "Simplification";
            $steps = "<br>1. {$varNames[0]} ∧ {$varNames[1]}: {$vars[$varNames[0]]} and {$vars[$varNames[1]]} (Premise)<br>2. Conclusion:<br> {$varNames[0]}: {$vars[$varNames[0]]}";
            $valid = true;
        }
        // Addition: If p, then p ∨ q.
        elseif (
            in_array("$varNames[0]", $parsedPremises) &&
            $parsedConclusion == "$varNames[0] ∨ $varNames[1]"
        ) {
            $inferenceRule = "Addition";
            $steps = "<br>1. {$varNames[0]}: {$vars[$varNames[0]]} (Premise)<br>2. Conclusion:<br> {$varNames[0]} ∨ {$varNames[1]}: {$vars[$varNames[0]]} or {$vars[$varNames[1]]}";
            $valid = true;
        }
        // Resolution: If p ∨ q and ¬p ∨ r, then q ∨ r.
        elseif (
            in_array("$varNames[0] ∨ $varNames[1]", $parsedPremises) &&
            in_array("¬$varNames[0] ∨ $varNames[2]", $parsedPremises) &&
            $parsedConclusion == "$varNames[1] ∨ $varNames[2]"
        ) {
            $inferenceRule = "Resolution";
            $steps = "<br>1. {$varNames[0]} ∨ {$varNames[1]} ({$vars[$varNames[0]]} or {$vars[$varNames[1]]})<br>2. ¬{$varNames[0]} ∨ {$varNames[2]} (Not {$vars[$varNames[0]]} or {$vars[$varNames[2]]})<br>3. Conclusion:<br> {$varNames[1]} ∨ {$varNames[2]}: {$vars[$varNames[1]]} or {$vars[$varNames[2]]}";
            $valid = true;
        }

        $ruleOutput = $inferenceRule;
        $stepsOutput = strip_tags(str_replace('<br>', "\n", $steps)); // Remove HTML tags for textarea
        $conclusionOutput = $valid ? "Valid" : "Invalid";
    }      

?>

        <!-- Result Section -->
        <div class="result-section">
            <h2>RESULT</h2>
            <div class="result-placeholder" id="resultPlaceholder" style="<?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo 'display:none;'; ?>">
                <!-- <img src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/svgs/solid/brain.svg" alt="Brain Icon" /> -->
                
                <img src="ROI.jpeg" />
                
            </div>
            <div class="result-content<?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo ' show'; ?>" id="resultContent">
                <div class="form-group">
                    <label for="premises-output">Premises :</label>
                    <textarea id="premises-output" class="readonly-input" readonly><?php echo isset($premisesOutput) ? $premisesOutput : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="rule-output">Rules of Inference :</label>
                    <textarea id="rule-output" class="readonly-input" readonly><?php echo isset($ruleOutput) ? $ruleOutput : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="steps-output">Steps :</label>
                    <textarea id="steps-output" class="readonly-input" readonly><?php echo isset($stepsOutput) ? $stepsOutput : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="result-conclusion">Conclusion :</label>
                    <textarea id="result-conclusion" class="readonly-input" readonly><?php echo isset($conclusionOutput) ? $conclusionOutput : ''; ?></textarea>
                </div>
             </div>
        </div>


    </div>
</body>
</html>