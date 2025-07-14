<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inference Engine - Logical Validity</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Inference Engine</h1>

        <!-- Step 1: Number of Premises -->
        <form method="POST" action="php/process.php">
            <label for="numPremises">Enter the number of premises:</label>
            <input type="number" id="numPremises" name="numPremises" min="1" required>
            <br><br>
            <!-- Step 2: Premise inputs based on the number of premises -->
            <div id="premiseInputs">
                <!-- Premise inputs will be dynamically added based on numPremises -->
            </div>
            <br><br>
            <!-- Step 3: Select rule of inference -->
            <label for="rule">Enter rule of inference to use:</label>
            <select name="rule" id="rule">
                <option value="1">Modus Ponens</option>
                <option value="2">Modus Tollens</option>
                <option value="3">Hypothetical Syllogism</option>
                <option value="4">Disjunctive Syllogism</option>
                <option value="5">Simplification</option>
                <option value="6">Conjunction</option>
                <option value="7">Addition</option>
                <option value="8">Resolution</option>
            </select>
            <br><br>
            <!-- Step 4: Enter statements -->
            <label for="p">Enter statement for premise p:</label>
            <input type="text" id="p" name="p" required>
            <br><br>
            <label for="q">Enter statement for premise q:</label>
            <input type="text" id="q" name="q" required>
            <br><br>
            <label for="r">Enter statement for premise r (optional):</label>
            <input type="text" id="r" name="r">
            <br><br>
            <!-- Step 5: Enter conclusion statement -->
            <label for="conclusion">Enter conclusion statement:</label>
            <input type="text" id="conclusion" name="conclusion" required>
            <br><br>
            <!-- Step 6: Submit the form -->
            <button type="submit">Apply Logic Rules</button>
        </form>

        <div id="result">
            <?php
                if (isset($_GET['result'])) {
                    echo "<p>" . htmlspecialchars($_GET['result']) . "</p>";
                }
            ?>
        </div>
    </div>

    <script>
        // Step 2: Dynamically create input fields based on the number of premises
        document.getElementById("numPremises").addEventListener("input", function() {
            var numPremises = parseInt(this.value);
            var premiseInputsDiv = document.getElementById("premiseInputs");
            premiseInputsDiv.innerHTML = ""; // Clear previous inputs

            for (let i = 1; i <= numPremises; i++) {
                var label = document.createElement("label");
                label.textContent = "Enter premise " + i + ":";
                var input = document.createElement("input");
                input.type = "text";
                input.name = "premise" + i;
                premiseInputsDiv.appendChild(label);
                premiseInputsDiv.appendChild(input);
            }
        });
    </script>
</body>
</html>
