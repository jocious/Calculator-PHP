let currentNumber = "0";

let firstNumber = null;

let operator = null;

let memory = 0;


const display = document.getElementById("display");

const history = document.getElementById("history");


// Update display

function updateDisplay() {

    display.textContent = currentNumber;

}


// Add number

function addNumber(number) {

    if (currentNumber === "0" && number !== ".") {

        currentNumber = number;

    } else {

        currentNumber += number;

    }

    updateDisplay();

}


// Choose operator

function chooseOperator(selectedOperator) {

    firstNumber = Number(currentNumber);

    operator = selectedOperator;

    history.textContent =
        firstNumber + " " + selectedOperator;

    currentNumber = "0";

}


// SEND CALCULATION TO PYTHON

async function calculate() {

    let secondNumber = Number(currentNumber);


    const response = await fetch("/calculate", {

        method: "POST",

        headers: {

            "Content-Type": "application/json"

        },

        body: JSON.stringify({

            firstNumber: firstNumber,

            secondNumber: secondNumber,

            operator: operator

        })

    });


    const data = await response.json();


    if (data.error) {

        currentNumber = data.error;

    } else {

        currentNumber = String(data.result);

    }


    history.textContent =
        firstNumber + " " +
        operator + " " +
        secondNumber + " =";


    firstNumber = null;

    operator = null;


    updateDisplay();

}


// Clear

function clearAll() {

    currentNumber = "0";

    firstNumber = null;

    operator = null;

    history.textContent = "";

    updateDisplay();

}


// CE

function clearEntry() {

    currentNumber = "0";

    updateDisplay();

}


// Backspace

function backspace() {

    if (currentNumber.length === 1) {

        currentNumber = "0";

    } else {

        currentNumber =
            currentNumber.slice(0, -1);

    }

    updateDisplay();

}


// Positive / Negative

function changeSign() {

    currentNumber =
        String(Number(currentNumber) * -1);

    updateDisplay();

}


// Percentage

function percent() {

    currentNumber =
        String(Number(currentNumber) / 100);

    updateDisplay();

}


// 1 / x

function reciprocal() {

    currentNumber =
        String(1 / Number(currentNumber));

    updateDisplay();

}


// x²

function square() {

    currentNumber =
        String(Number(currentNumber) ** 2);

    updateDisplay();

}


// √x

function squareRoot() {

    currentNumber =
        String(Math.sqrt(Number(currentNumber)));

    updateDisplay();

}


// Memory

function memoryClear() {

    memory = 0;

}


function memoryRecall() {

    currentNumber = String(memory);

    updateDisplay();

}


function memoryAdd() {

    memory += Number(currentNumber);

}


function memorySubtract() {

    memory -= Number(currentNumber);

}


function memorySave() {

    memory = Number(currentNumber);

}