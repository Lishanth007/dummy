<?php
$result = '';
$expression = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['expression'])) {
        $expression = $_POST['expression'];
        
        // Remove spaces
        $expression = str_replace(' ', '', $expression);
        
        // ✅ Enhanced security: Only allow digits, math operators, parentheses, and dots
        if (preg_match('/^[0-9+\-*\/.()]+$/', $expression)) {
            // Additional security checks
            if (substr_count($expression, '(') !== substr_count($expression, ')')) {
                $result = "Error: Unbalanced parentheses";
            } else if (preg_match('/(\/0(\.0*)?([^0-9]|$))/', $expression)) {
                $result = "Error: Division by zero";
            } else if (preg_match('/[+\-*\/.]{2,}/', $expression)) {
                $result = "Error: Invalid operator sequence";
            } else {
                // Safe evaluation using a parsing function
                $result = evaluateExpression($expression);
            }
        } else {
            $result = "Invalid input";
        }
    }
}

/**
 * Safe mathematical expression evaluation
 */
function evaluateExpression($expression) {
    // Remove any potentially dangerous patterns
    $expression = preg_replace('/[^0-9+\-*\/.()]/', '', $expression);
    
    try {
        // Check for empty expression
        if (empty($expression)) return '';
        
        // Final validation before evaluation
        if (!preg_match('/^[0-9+\-*\/.()]+$/', $expression)) {
            return "Error: Invalid characters";
        }
        
        // Use create_function as a slightly safer alternative (though deprecated)
        // In production, consider using a proper math parser library
        $result = @eval("return $expression;");
        
        if ($result === false || $result === null) {
            return "Error";
        }
        
        // Format the result
        if (is_float($result) && $result == (int)$result) {
            return (int)$result;
        }
        
        return $result;
        
    } catch (Throwable $e) {
        return "Error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Calculator | ClickToBill</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1A2035, #1A2035);
    min-height: 100vh;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }

  .calculator {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    width: 340px;
    padding: 20px;
    text-align: center;
  }

  .display {
    background: #f4f6ff;
    color: #2c3e50;
    font-size: 2rem;
    border: none;
    border-radius: 10px;
    width: 100%;
    height: 60px;
    text-align: right;
    padding: 10px;
    margin-bottom: 15px;
    outline: none;
    box-sizing: border-box;
  }

  .buttons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }

  button {
    background-color: #4A6CF7;
    color: white;
    font-size: 1.3rem;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  button:hover {
    background-color: #2E52D9;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(52,152,219,0.3);
  }

  .operator {
    background-color: #6C8EF8;
  }

  .equal {
    background-color: #34c759;
  }

  .equal:hover {
    background-color: #28a745;
  }

  .clear {
    background-color: #f93c3c;
  }

  .clear:hover {
    background-color: #d62828;
  }

  .back-link {
    display: inline-block;
    margin-top: 20px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    transition: opacity 0.3s;
    padding: 10px 20px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
  }

  .back-link:hover {
    opacity: 0.8;
    background: rgba(255,255,255,0.3);
  }
</style>
</head>
<body>

<div>
  <form method="POST" class="calculator" id="calcForm">
    <input type="text" class="display" id="display" name="expression"
           value="<?php echo htmlspecialchars($result !== '' ? $result : $expression); ?>" readonly>

    <div class="buttons">
      <button type="button" onclick="press('7')">7</button>
      <button type="button" onclick="press('8')">8</button>
      <button type="button" onclick="press('9')">9</button>
      <button type="button" class="operator" onclick="press('/')">÷</button>

      <button type="button" onclick="press('4')">4</button>
      <button type="button" onclick="press('5')">5</button>
      <button type="button" onclick="press('6')">6</button>
      <button type="button" class="operator" onclick="press('*')">×</button>

      <button type="button" onclick="press('1')">1</button>
      <button type="button" onclick="press('2')">2</button>
      <button type="button" onclick="press('3')">3</button>
      <button type="button" class="operator" onclick="press('-')">−</button>

      <button type="button" onclick="press('0')">0</button>
      <button type="button" onclick="press('.')">.</button>
      <button type="button" class="clear" onclick="clearDisplay()">C</button>
      <button type="button" class="operator" onclick="press('+')">+</button>

      <button type="button" onclick="press('(')">(</button>
      <button type="button" onclick="press(')')">)</button>
      <button type="button" class="clear" onclick="backspace()">⌫</button>
      <button type="submit" class="equal">=</button>
    </div>
  </form>

  <a href="index.html" class="back-link">← Back to Tools</a>
</div>

<script>
  const display = document.getElementById('display');

  function press(value) {
    // Only clear if there's an error message
    if (display.value === 'Error' || display.value === 'Invalid input' || 
        display.value === 'Error: Unbalanced parentheses' || 
        display.value === 'Error: Division by zero' || 
        display.value === 'Error: Invalid operator sequence' ||
        display.value === 'Error: Invalid characters') {
      display.value = '';
    }
    
    // Don't clear for normal number input - allow multiple numbers to be entered
    display.value += value;
  }

  function clearDisplay() {
    display.value = '';
  }

  function backspace() {
    display.value = display.value.slice(0, -1);
  }

  // Allow keyboard input
  document.addEventListener('keydown', function(event) {
    const key = event.key;
    
    if (/[0-9+\-*/.()]/.test(key)) {
      event.preventDefault();
      press(key);
    } else if (key === 'Enter') {
      event.preventDefault();
      document.getElementById('calcForm').dispatchEvent(new Event('submit'));
    } else if (key === 'Escape' || key === 'Delete') {
      event.preventDefault();
      clearDisplay();
    } else if (key === 'Backspace') {
      event.preventDefault();
      backspace();
    }
  });
</script>

</body>
</html>