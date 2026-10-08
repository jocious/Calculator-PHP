<?php
session_start();

// If currentNumber does not exist yet,
// start the calculator at 0.
if (!isset($_SESSION["currentNumber"])) {
    $_SESSION["currentNumber"] = "0";
}

// Store the first number.
if (!isset($_SESSION["firstNumber"])) {
    $_SESSION["firstNumber"] = null;
}

// Store the selected operator.
if (!isset($_SESSION["operator"])) {
    $_SESSION["operator"] = null;
}

// Store the calculator history.
if (!isset($_SESSION["history"])) {
    $_SESSION["history"] = "";
}

// Get Calculator Values
$currentNumber = $_SESSION["currentNumber"];
$firstNumber = $_SESSION["firstNumber"];
$operator = $_SESSION["operator"];
$history = $_SESSION["history"];

// Check if a Button was Pressed 
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the value sent by the button.
    $button = $_POST["button"] ?? "";

    // Number Buttons
    if (
        $button == "0" ||
        $button == "1" ||
        $button == "2" ||
        $button == "3" ||
        $button == "4" ||
        $button == "5" ||
        $button == "6" ||
        $button == "7" ||
        $button == "8" ||
        $button == "9"
    ) {

        // Replace the first zero.
        if ($currentNumber == "0") {
            $currentNumber = $button;
        }

        // Add the number to the current number.
        else {
            $currentNumber .= $button;
        }
    }

    // Decimal Point
    elseif ($button == ".") {

        // Only add decimal point if
        // one does not already exist.
        if (strpos($currentNumber, ".") === false) {

            $currentNumber .= ".";
        }
    }

    // Percentage
    elseif ($button == "percent") {

        // Divide the current number by 100.
        $currentNumber =
            (string)((float)$currentNumber / 100);
    }

    // Clear Entry
    elseif ($button == "CE") {

        // Clear only the current number.
        $currentNumber = "0";
    }

    // Clear All
    elseif ($button == "C") {

        // Reset everything.
        $currentNumber = "0";
        $firstNumber = null;
        $operator = null;
        $history = "";
    }

    // Backspace
    elseif ($button == "backspace") {

        // Remove the last character.
        $currentNumber =
            substr($currentNumber, 0, -1);

        // If nothing remains, return to zero.
        if ($currentNumber == "") {
            $currentNumber = "0";
        }
    }
    // Change Sign
    elseif ($button == "sign") {

        // Do not change zero.
        if ($currentNumber != "0") {

            // Change positive to negative or negative to positive.
            $currentNumber =
                (string)((float)$currentNumber * -1);
        }
    }

    // Choose Operator
    elseif (
        $button == "+" ||
        $button == "-" ||
        $button == "*" ||
        $button == "/"
    ) {

        // Save the first number.
        $firstNumber = $currentNumber;

        // Save the operator.
        $operator = $button;

        // Show the calculation in history.
        $history =
            $currentNumber . " " . $operator;

        // Start the second number at zero.
        $currentNumber = "0";
    }

    // Calculate
    elseif ($button == "=") {

        // Make sure there is a first number and an operator.
        if (
            $firstNumber !== null &&
            $operator !== null
        ) {

            // Save the second number.
            $secondNumber = $currentNumber;

            // Convert the numbers to decimals.
            $number1 = (float)$firstNumber;
            $number2 = (float)$secondNumber;

            // Store the result.
            $result = 0;

            // Addition
            if ($operator == "+") {

                $result =
                    $number1 + $number2;
            }

            // Subtraction
            elseif ($operator == "-") {

                $result =
                    $number1 - $number2;
            }

            // Multiplication
            elseif ($operator == "*") {

                $result =
                    $number1 * $number2;
            }

            // Division
            elseif ($operator == "/") {

                // Prevent division by zero.
                if ($number2 == 0) {

                    $currentNumber =
                        "Cannot divide by zero";

                    $history = "";
                    $firstNumber = null;
                    $operator = null;
                }

                else {

                    $result =
                        $number1 / $number2;
                }
            }

            // Show Result
            if (
                $currentNumber !=
                "Cannot divide by zero"
            ) {

                // Show complete calculation.
                $history =
                    $firstNumber . " " .
                    $operator . " " .
                    $secondNumber . " =";

                // Store result.
                $currentNumber =
                    (string)$result;

                // Remove .0 from whole numbers.
                if (
                    is_numeric($currentNumber) &&
                    (float)$currentNumber ==
                    (int)$currentNumber
                ) {

                    $currentNumber =
                        (string)(int)$currentNumber;
                }

                // Clear first number.
                $firstNumber = null;

                // Clear operator.
                $operator = null;
            }
        }
    }

    // Save Values to Session
    $_SESSION["currentNumber"] =
        $currentNumber;

    $_SESSION["firstNumber"] =
        $firstNumber;

    $_SESSION["operator"] =
        $operator;

    $_SESSION["history"] =
        $history;
}

?>


<!DOCTYPE html>
<html>
<head>

    <!-- Name shown on the browser tab -->
    <title>PHP Calculator</title>

    <!-- Connect HTML to CSS -->
    <link rel="stylesheet" href="style.css">

</head>
<body>


    <!-- Main calculator -->
    <div class="calculator">

        <!-- Top bar -->
        <div class="top">

            <!-- Menu icon -->
            <span>☰</span>

            <!-- Calculator type -->
            <b>Standard</b>

        </div>


        <!-- Display -->
        <div class="display">

            <!-- Shows previous calculation -->
            <div id="history">

                <?php
                echo htmlspecialchars($history);
                ?>
            </div>

            <!-- Shows current number -->
            <div id="display">
                <?php
                echo htmlspecialchars($currentNumber);
                ?>
            </div>
        </div>


        <!-- Memory buttons -->
        <div class="memory">
            <!-- Disabled memory buttons -->
            <button disabled>MC</button>
            <button disabled>MR</button>
            <button disabled>M+</button>
            <button disabled>M−</button>
            <button disabled>MS</button>
            <button disabled>M⌄</button>
        </div>


        <!-- One form contains all calculator buttons -->
        <form method="POST">

            <!-- Calculator buttons -->
            <div class="buttons">

                <!-- First Row -->
                <button
                    type="submit"
                    name="button"
                    value="percent"
                >
                    %
                </button>

                <button
                    type="submit"
                    name="button"
                    value="CE"
                >
                    CE
                </button>

                <button
                    type="submit"
                    name="button"
                    value="C"
                >
                    C
                </button>

                <button
                    type="submit"
                    name="button"
                    value="backspace"
                >
                    ⌫
                </button>

                <!-- Second Row -->
                <!-- Disabled 1/x -->
                <button
                    type="button"
                    class="disabled-button"
                    disabled
                >
                    ¹⁄ₓ
                </button>
                
                <!-- Disabled x² -->
                <button
                    type="button"
                    class="disabled-button"
                    disabled
                >
                    x²
                </button>


                <!-- Disabled square root -->
                <button
                    type="button"
                    class="disabled-button"
                    disabled
                >
                    √x
                </button>


                <!-- Division -->
                <button
                    type="submit"
                    name="button"
                    value="/"
                >
                    ÷
                </button>

                <!-- Third Row -->
                <button
                    type="submit"
                    name="button"
                    value="7"
                >
                    7
                </button>


                <button
                    type="submit"
                    name="button"
                    value="8"
                >
                    8
                </button>


                <button
                    type="submit"
                    name="button"
                    value="9"
                >
                    9
                </button>


                <button
                    type="submit"
                    name="button"
                    value="*"
                >
                    ×
                </button>

                <!-- Fourth Row -->
                <button
                    type="submit"
                    name="button"
                    value="4"
                >
                    4
                </button>


                <button
                    type="submit"
                    name="button"
                    value="5"
                >
                    5
                </button>


                <button
                    type="submit"
                    name="button"
                    value="6"
                >
                    6
                </button>


                <button
                    type="submit"
                    name="button"
                    value="-"
                >
                    −
                </button>

                <!-- Fifth Row -->
                <button
                    type="submit"
                    name="button"
                    value="1"
                >
                    1
                </button>


                <button
                    type="submit"
                    name="button"
                    value="2"
                >
                    2
                </button>


                <button
                    type="submit"
                    name="button"
                    value="3"
                >
                    3
                </button>


                <button
                    type="submit"
                    name="button"
                    value="+"
                >
                    +
                </button>

                <!-- Last Row -->
                <button
                    type="submit"
                    name="button"
                    value="sign"
                >
                    +/−
                </button>


                <button
                    type="submit"
                    name="button"
                    value="0"
                >
                    0
                </button>


                <button
                    type="submit"
                    name="button"
                    value="."
                >
                    .
                </button>


                <!-- Equals -->
                <button
                    type="submit"
                    class="equals"
                    name="button"
                    value="="
                >
                    =
                </button>
            </div>
        </form>
    </div>
</body>
</html>