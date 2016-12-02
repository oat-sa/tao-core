<div class="calcContainer">
    <form action="">
        <input type="text" class="calcDisplay" readonly="readonly"/>

        <button type="button" value="%" data-key="%" class="calcFunction calcFirst calcPercentage">&#37;</button>
        <button type="button" value="sqrt" data-key="sqrt" class="calcFunction calcSqrt">&radic;</button>
        <button type="button" value="pow" data-key="pow" class="calcFunction calcPower">x<sup class="letter">y</sup></button>
        <button type="button" value="1/x" data-key="1/x" class="calcFunction calcInverse"><sup class="digit">1</sup>&frasl;<sub>x</sub></button>

        <button type="button" value="C" data-key="C" class="calcClear calcFirst">C</button>
        <button type="button" value="CE" data-key="CE" class="calcClear">CE</button>
        <button type="button" value="DEL" data-key="DEL" class="calcClear">DEL</button>
        <button type="button" value="/" data-key="/" class="calcFunction calcDivision">&divide;</button>

        <button type="button" value="7" data-key="7" class="calcInput calcDigit calcFirst">7</button>
        <button type="button" value="8" data-key="8" class="calcInput calcDigit">8</button>
        <button type="button" value="9" data-key="9" class="calcInput calcDigit">9</button>
        <button type="button" value="*" data-key="*" class="calcFunction calcMultiplication">&times;</button>

        <button type="button" value="4" data-key="4" class="calcFirst calcDigit calcInput">4</button>
        <button type="button" value="5" data-key="5" class="calcInput calcDigit">5</button>
        <button type="button" value="6" data-key="6" class="calcInput calcDigit">6</button>
        <button type="button" value="-" data-key="-" class="calcFunction calcSubtraction">&minus;</button>

        <button type="button" value="1" data-key="1" class="calcFirst calcDigit calcInput">1</button>
        <button type="button" value="2" data-key="2" class="calcInput calcDigit">2</button>
        <button type="button" value="3" data-key="3" class="calcInput calcDigit">3</button>
        <button type="button" value="+" data-key="+" class="calcFunction calcSum">+</button>

        <button type="button" value="+/-" data-key="+/-" class="calcFirst calcInput calcPlusMinus">&plusmn;</button>
        <button type="button" value="0" data-key="0" class="calcInput calcDigit">0</button>
        <button type="button" value="." data-key="." class="calcInput calcDot">.</button>
        <input type="submit" value="=" data-key="=" class="calcFunction calcSubmit calcEqual"/>
    </form>
</div>