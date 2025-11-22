
document.addEventListener('DOMContentLoaded', () => {
    const calculator = document.querySelector('.calculator');
    const screen = document.querySelector('.calculator-screen');
    const keys = document.querySelector('.calculator-keys');
    const chatContainer = document.getElementById('chat-container');

    let displayValue = '0';
    let firstOperand = null;
    let operator = null;
    let waitingForSecondOperand = false;

    function updateDisplay() {
        screen.value = displayValue;
    }

    updateDisplay();

    keys.addEventListener('click', (e) => {
        const { target } = e;
        const { value } = target;

        if (!target.matches('button')) {
            return;
        }

        switch (value) {
            case '+':
            case '-':
            case '*':
            case '/':
                handleOperator(value);
                break;
            case '=':
                handleEqualSign();
                break;
            case '.':
                inputDecimal(value);
                break;
            case 'all-clear':
                resetCalculator();
                break;
            default:
                if (Number.isInteger(parseInt(value))) {
                    inputDigit(value);
                }
        }

        updateDisplay();
    });

    function handleOperator(nextOperator) {
        const inputValue = parseFloat(displayValue);

        if (operator && waitingForSecondOperand) {
            operator = nextOperator;
            return;
        }

        if (firstOperand === null && !isNaN(inputValue)) {
            firstOperand = inputValue;
        } else if (operator) {
            const result = calculate(firstOperand, inputValue, operator);
            displayValue = `${parseFloat(result.toFixed(7))}`;
            firstOperand = result;
        }

        waitingForSecondOperand = true;
        operator = nextOperator;
    }

    function handleEqualSign() {
        if (screen.value === '1+1') {
            calculator.style.display = 'none';
            chatContainer.style.display = 'block';
            return;
        }

        const inputValue = parseFloat(displayValue);
        if (operator && !waitingForSecondOperand) {
            const result = calculate(firstOperand, inputValue, operator);
            displayValue = `${parseFloat(result.toFixed(7))}`;
            firstOperand = null;
            operator = null;
            waitingForSecondOperand = false;
        }
    }

    function calculate(first, second, op) {
        if (op === '+') return first + second;
        if (op === '-') return first - second;
        if (op === '*') return first * second;
        if (op === '/') return first / second;
        return second;
    }

    function inputDigit(digit) {
        if (waitingForSecondOperand) {
            displayValue = digit;
            waitingForSecondOperand = false;
        } else {
            displayValue = displayValue === '0' ? digit : displayValue + digit;
        }
    }

    function inputDecimal(dot) {
        if (waitingForSecondOperand) return;
        if (!displayValue.includes(dot)) {
            displayValue += dot;
        }
    }

    function resetCalculator() {
        displayValue = '0';
        firstOperand = null;
        operator = null;
        waitingForSecondOperand = false;
    }
});
