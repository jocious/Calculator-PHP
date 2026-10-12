
<?php
// Start a session to remember calculator values between clicks.
session_start();

// Set the starting values when the calculator first opens.
if (!isset($_SESSION["current"])) {
    $_SESSION["current"] = "0";
    $_SESSION["first"] = null;
    $_SESSION["operator"] = null;
    $_SESSION["history"] = "";
    $_SESSION["newEntry"] = true;
}

// Format numbers so whole numbers do not show unnecessary .0.
function formatNumber($number) {
    if (!is_finite((float)$number)) {
        return "Error";
    }

    if (abs($number) < 0.000000000001) {
        $number = 0;
    }

    if (
        $number == floor($number) &&
        abs($number) < 1000000000000000
    ) {
        return (string)(int)$number;
    }

    return rtrim(rtrim(sprintf("%.10f", $number), "0"), ".");
}

// Get the saved calculator values.
$current = $_SESSION["current"];
$first = $_SESSION["first"];
$operator = $_SESSION["operator"];
$history = $_SESSION["history"];
$newEntry = $_SESSION["newEntry"];

// Check whether a calculator button was pressed.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the value of the pressed button.
    $button = $_POST["button"] ?? "";

    // NUMBER BUTTONS: 0 to 9.
    if (preg_match('/^[0-9]$/', $button)) {

        // Start a new number when necessary.
        if ($newEntry || $current === "0" || $current === "Error") {
            $current = $button;
            $newEntry = false;
        } else {
            // Add the digit to the end of the current number.
            if (strlen($current) < 15) {
                $current .= $button;
            }
        }
    }

    // DECIMAL POINT.
    elseif ($button === ".") {
        if ($newEntry || $current === "Error") {
            $current = "0.";
            $newEntry = false;
        } elseif (strpos($current, ".") === false) {
            $current .= ".";
        }
    }

    // CE: Clear the current entry only.
    elseif ($button === "CE") {
        $current = "0";
        $newEntry = true;
    }

    // C: Clear the entire calculation.
    elseif ($button === "C") {
        $current = "0";
        $first = null;
        $operator = null;
        $history = "";
        $newEntry = true;
    }

    // BACKSPACE: Delete the last digit.
    elseif ($button === "backspace") {

        // Delete only while entering a number.
        if (!$newEntry && is_numeric($current)) {

            // Remove the last character from the display.
            $current = substr($current, 0, -1);

            // Show zero if no characters remain.
            if ($current === "" || $current === "-") {
                $current = "0";
            }
        }
    }

    // CHANGE SIGN: Positive becomes negative and vice versa.
    elseif ($button === "sign") {
        if (is_numeric($current) && (float)$current != 0) {
            $current = formatNumber((float)$current * -1);
        }
    }

    // PERCENTAGE: Divide the current number by 100.
    elseif ($button === "percent") {
        if (is_numeric($current)) {
            $current = formatNumber((float)$current / 100);
        }
        $newEntry = true;
    }

    // RECIPROCAL: Calculate 1 divided by the current number.
    elseif ($button === "reciprocal") {
        if (is_numeric($current)) {
            if ((float)$current == 0) {
                $current = "Error";
                $history = "Cannot divide by zero";
            } else {
                $history = "1 / " . $current . " =";
                $current = formatNumber(1 / (float)$current);
            }
        }

        $first = null;
        $operator = null;
        $newEntry = true;
    }

    // SQUARE: Multiply the number by itself.
    elseif ($button === "square") {
        if (is_numeric($current)) {
            $history = "(" . $current . ")² =";
            $current = formatNumber((float)$current * (float)$current);
        }

        $first = null;
        $operator = null;
        $newEntry = true;
    }

    // SQUARE ROOT: Calculate the square root.
    elseif ($button === "sqrt") {
        if (is_numeric($current)) {
            if ((float)$current < 0) {
                $current = "Error";
                $history = "Invalid square root";
            } else {
                $history = "√(" . $current . ") =";
                $current = formatNumber(sqrt((float)$current));
            }
        }

        $first = null;
        $operator = null;
        $newEntry = true;
    }

    // ARITHMETIC OPERATORS: +, -, *, /.
    elseif (in_array($button, ["+", "-", "*", "/"], true)) {

        // Calculate the previous operation if needed.
        if ($first !== null && $operator !== null && !$newEntry) {
            $a = (float)$first;
            $b = (float)$current;

            if ($operator === "+") {
                $result = $a + $b;
            } elseif ($operator === "-") {
                $result = $a - $b;
            } elseif ($operator === "*") {
                $result = $a * $b;
            } else {
                $result = ($b == 0) ? null : $a / $b;
            }

            if ($result === null) {
                $current = "Error";
                $history = "Cannot divide by zero";
                $first = null;
                $operator = null;
            } else {
                $current = formatNumber($result);
                $first = $current;
            }
        } else {
            // Save the current number as the first number.
            $first = $current;
        }

        // Save the selected operator.
        if ($current !== "Error") {
            $operator = $button;

            // Use calculator symbols in the history display.
            $symbol = $button === "*" ? "×" :
                ($button === "/" ? "÷" : $button);

            $history = $current . " " . $symbol;
            $newEntry = true;
        }
    }

    // EQUALS: Calculate the answer.
    elseif ($button === "=") {
        if (
            $first !== null &&
            $operator !== null &&
            is_numeric($current)
        ) {
            $a = (float)$first;
            $b = (float)$current;

            // Perform the selected calculation.
            if ($operator === "+") {
                $result = $a + $b;
            } elseif ($operator === "-") {
                $result = $a - $b;
            } elseif ($operator === "*") {
                $result = $a * $b;
            } else {
                $result = ($b == 0) ? null : $a / $b;
            }

            if ($result === null) {
                // Prevent division by zero.
                $current = "Error";
                $history = "Cannot divide by zero";
            } else {
                // Display the full calculation above the answer.
                $symbol = $operator === "*" ? "×" :
                    ($operator === "/" ? "÷" : $operator);

                $history = $first . " " . $symbol .
                    " " . $current . " =";

                // Display the final answer.
                $current = formatNumber($result);
            }

            // Reset the pending operation.
            $first = null;
            $operator = null;
            $newEntry = true;
        }
    }

    // Save the updated calculator values.
    $_SESSION["current"] = $current;
    $_SESSION["first"] = $first;
    $_SESSION["operator"] = $operator;
    $_SESSION["history"] = $history;
    $_SESSION["newEntry"] = $newEntry;

    // Refresh without resubmitting the previous button.
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Browser tab title -->
    <title>PHP Calculator</title>

    <!-- Connect the CSS file -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Main calculator box -->
    <main class="calculator">

        <!-- Calculator heading -->
        <header class="top">
            <span class="menu-icon">☰</span>
            <h1>Calculator</h1>
        </header>

        <!-- Display area -->
        <section class="display">

            <!-- Show the previous calculation -->
            <div class="history">
                <?= htmlspecialchars($history, ENT_QUOTES, "UTF-8") ?>
            </div>

            <!-- Show the current number or answer -->
            <div class="current">
                <?= htmlspecialchars($current, ENT_QUOTES, "UTF-8") ?>
            </div>

        </section>

        <!-- All calculator buttons -->
        <form method="POST" class="buttons">

            <!-- First row -->
            <button type="submit" name="button" value="percent">%</button>
            <button type="submit" name="button" value="CE">CE</button>
            <button type="submit" name="button" value="C">C</button>
            <button type="submit" name="button" value="backspace">⌫</button>

            <!-- Second row -->
            <button type="submit" name="button" value="reciprocal">¹⁄ₓ</button>
            <button type="submit" name="button" value="square">x²</button>
            <button type="submit" name="button" value="sqrt">√x</button>
            <button type="submit" name="button" value="/">÷</button>

            <!-- Third row -->
            <button type="submit" name="button" value="7">7</button>
            <button type="submit" name="button" value="8">8</button>
            <button type="submit" name="button" value="9">9</button>
            <button type="submit" name="button" value="*">×</button>

            <!-- Fourth row -->
            <button type="submit" name="button" value="4">4</button>
            <button type="submit" name="button" value="5">5</button>
            <button type="submit" name="button" value="6">6</button>
            <button type="submit" name="button" value="-">−</button>

            <!-- Fifth row -->
            <button type="submit" name="button" value="1">1</button>
            <button type="submit" name="button" value="2">2</button>
            <button type="submit" name="button" value="3">3</button>
            <button type="submit" name="button" value="+">+</button>

            <!-- Last row -->
            <button type="submit" name="button" value="sign">+/−</button>
            <button type="submit" name="button" value="0">0</button>
            <button type="submit" name="button" value=".">.</button>
            <button type="submit" name="button" value="=" class="equals">=</button>

        </form>

    </main>

</body>
</html>
