from flask import Flask, render_template, request, jsonify

app = Flask(__name__)


@app.route("/")
def home():
    return render_template("index.html")


@app.route("/calculate", methods=["POST"])
def calculate():

    data = request.json

    first_number = data["firstNumber"]
    second_number = data["secondNumber"]
    operator = data["operator"]

    result = 0

    if operator == "+":
        result = first_number + second_number

    elif operator == "-":
        result = first_number - second_number

    elif operator == "*":
        result = first_number * second_number

    elif operator == "/":

        if second_number == 0:
            return jsonify({
                "error": "Cannot divide by zero"
            })

        result = first_number / second_number

    return jsonify({
        "result": result
    })


if __name__ == "__main__":
    app.run(debug=True)