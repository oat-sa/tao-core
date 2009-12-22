/*
* The DashedLine class provides a means to draw with standard drawing
* methods but allows you to draw using dashed lines. Dashed lines are continuous
* between drawing commands so dashes won't be interrupted when new lines
* are drawn in succession
* Use a DashedLine instance just as you would use a movie clip to draw lines
* and fills using Flash's drawing API on a movie clip.
*
* v 1.0.2 usage
* <pre><code>import com.eXULiS.lib.DashedLine;
* // create a DashedLine instance that draws in _root
* // dashes will be 10px long, spaces between will be 5
* var myDashedDrawing:DashedLine = new DashedLine(_root, 10, 5);
* // draw a square with dashed lines
* myDashedDrawing.moveTo(50, 50);
* myDashedDrawing.lineTo(100, 50);
* myDashedDrawing.lineTo(100, 100);
* myDashedDrawing.lineTo(50, 100);
* myDashedDrawing.lineTo(50, 50);
* 
* v 1.1 usage
* // constructor and setDash changed to be compliant with SVG 1.1 specs
* // pairs of length,gap ex: 10,5,4,5 cf: http://www.w3.org/TR/SVG/painting.html#StrokeDasharrayProperty
* var myDashedDrawing:DashedLine = new DashedLine(_root, "10,5");
*
* version 1.0.2 by Trevor McCauley, senocular.com
* version 1.1 by Rja
* 
*/
class com.eXULiS.lib.DashedLine {
	/**
	 * The target movie clip in which drawings are to be made
	 */
	public var target:Object;
	/**
	 * A value representing the accuracy used in determining the length
	 * of curveTo curves.
	 */
	public var _curveaccuracy:Number = 6;
		
	private var isLine:Boolean = true;
	private var overflow:Number = 0;
	private var offLength:Number = 0;
	private var onLength:Number = 0;
	private var dashLength:Number = 0;
	private var pen:Object;
	
	// constructor
	/**
	 * Class constructor; creates a ProgressiveDrawing instance.
	 * @param target The target movie clip to draw in.
	 * @param onLength Length of visible dash lines.
	 * @param offLength Length of space between dash lines.
	 */
	function DashedLine(target:Object, onLength:Number, offLength:Number){
		this.target = target;
		this.setDash(onLength, offLength);
		this.isLine = true;
		this.overflow = 0;
		this.pen = {x:0, y:0};
	}
	
	// public methods
	/**
	 * Sets new lengths for dash sizes
	 * @param onLength Length of visible dash lines.
	 * @param offLength Length of space between dash lines.
	 * @return nothing
	 */
	public function setDash(onLength:Number, offLength:Number):Void {
		this.onLength = onLength;
		this.offLength = offLength;
		this.dashLength = this.onLength + this.offLength;
	}
	/**
	 * Gets the current lengths for dash sizes
	 * @return Array containing the onLength and offLength values
	 * respectively in that order
	 */
	public function getDash(Void):Array {
		return [this.onLength, this.offLength];
	}
	/**
	 * Moves the current drawing position in target to (x, y).
	 * @param x An integer indicating the horizontal position relative to the registration point of
	 * the parent movie clip.
	 * @param An integer indicating the vertical position relative to the registration point of the
	 * parent movie clip.
	 * @return nothing
	 */
	public function moveTo(x:Number, y:Number):Void {
		this.targetMoveTo(x, y);
	}
	/**
	 * Draws a dashed line in target using the current line style from the current drawing position
	 * to (x, y); the current drawing position is then set to (x, y).
	 * @param x An integer indicating the horizontal position relative to the registration point of
	 * the parent movie clip.
	 * @param y An integer indicating the vertical position relative to the registration point of the
	 * parent movie clip.
	 * @return nothing
	 */
	public function lineTo(x:Number,y:Number):Void {
		var dx = x-this.pen.x;
		var dy = y-this.pen.y;
		var a = Math.atan2(dy, dx);
		var ca = Math.cos(a);
		var sa = Math.sin(a);
		var segLength = this.lineLength(dx, dy);
		if (this.overflow){
			if (this.overflow > segLength){
				if (this.isLine){
					this.targetLineTo(x, y);
				}
				else{
					this.targetMoveTo(x, y);
				}
				this.overflow -= segLength;
				return;
			}
			if (this.isLine){
				this.targetLineTo(this.pen.x + ca*this.overflow, this.pen.y + sa*this.overflow);
			}
			else{
				this.targetMoveTo(this.pen.x + ca*this.overflow, this.pen.y + sa*this.overflow);
			}
			segLength -= this.overflow;
			this.overflow = 0;
			this.isLine = !this.isLine;
			if (!segLength) return;
		}
		var fullDashCount = Math.floor(segLength/this.dashLength);
		if (fullDashCount){
			var onx = ca*this.onLength;
			var ony = sa*this.onLength;
			var offx = ca*this.offLength;
			var offy = sa*this.offLength;
			for (var i=0; i<fullDashCount; i++){
				if (this.isLine){
					this.targetLineTo(this.pen.x+onx, this.pen.y+ony);
					this.targetMoveTo(this.pen.x+offx, this.pen.y+offy);
				}else{
					this.targetMoveTo(this.pen.x+offx, this.pen.y+offy);
					this.targetLineTo(this.pen.x+onx, this.pen.y+ony);
				}
			}
			segLength -= this.dashLength*fullDashCount;
		}
		if (this.isLine){
			if (segLength > this.onLength){
				this.targetLineTo(this.pen.x+ca*this.onLength, this.pen.y+sa*this.onLength);
				this.targetMoveTo(x, y);
				this.overflow = this.offLength-(segLength-this.onLength);
				this.isLine = false;
			}else{
				this.targetLineTo(x, y);
				if (segLength == this.onLength){
					this.overflow = 0;
					this.isLine = !this.isLine;
				}else{
					this.overflow = this.onLength-segLength;
					this.targetMoveTo(x, y);
				}
			}
		}else{
			if (segLength > this.offLength){
				this.targetMoveTo(this.pen.x+ca*this.offLength, this.pen.y+sa*this.offLength);
				this.targetLineTo(x, y);
				this.overflow = this.onLength-(segLength-this.offLength);
				this.isLine = true;
			}else{
				this.targetMoveTo(x, y);
				if (segLength == this.offLength){
					this.overflow = 0;
					this.isLine = !this.isLine;
				}else this.overflow = this.offLength-segLength;
			}
		}
	}
	/**
	 * Draws a dashed curve in target using the current line style from the current drawing position to
	 * (x, y) using the control point specified by (cx, cy). The current  drawing position is then set
	 * to (x, y).
	 * @param cx An integer that specifies the horizontal position of the control point relative to
	 * the registration point of the parent movie clip.
	 * @param cy An integer that specifies the vertical position of the control point relative to the
	 * registration point of the parent movie clip.
	 * @param x An integer that specifies the horizontal position of the next anchor point relative
	 * to the registration. point of the parent movie clip.
	 * @param y An integer that specifies the vertical position of the next anchor point relative to
	 * the registration point of the parent movie clip.
	 * @return nothing
	 */
	public function curveTo(cx:Number, cy:Number, x:Number, y:Number):Void {
		var sx:Number = this.pen.x;
		var sy:Number = this.pen.y;
		var segLength = this.curveLength(sx, sy, cx, cy, x, y);
		var t:Number = 0;
		var t2:Number = 0;
		var c:Array;
		if (this.overflow){
			if (this.overflow > segLength){
				if (this.isLine) this.targetCurveTo(cx, cy, x, y);
				else this.targetMoveTo(x, y);
				this.overflow -= segLength;
				return;
			}
			t = this.overflow/segLength;
			c = this.curveSliceUpTo(sx, sy, cx, cy, x, y, t);
			if (this.isLine){
				this.targetCurveTo(c[2], c[3], c[4], c[5]);
			}
			else{
				this.targetMoveTo(c[4], c[5]);
			}
			this.overflow = 0;
			this.isLine = !this.isLine;
			if (!segLength){
				return;
			}
		}
		var remainLength = segLength - segLength*t;
		var fullDashCount = Math.floor(remainLength/this.dashLength);
		var ont = this.onLength/segLength;
		var offt = this.offLength/segLength;
		if (fullDashCount){
			for (var i=0; i<fullDashCount; i++){
				if (this.isLine){
					t2 = t + ont;
					c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
					this.targetCurveTo(c[2], c[3], c[4], c[5]);
					t = t2;
					t2 = t + offt;
					c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
					this.targetMoveTo(c[4], c[5]);
				}else{
					t2 = t + offt;
					c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
					this.targetMoveTo(c[4], c[5]);
					t = t2;
					t2 = t + ont;
					c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
					this.targetCurveTo(c[2], c[3], c[4], c[5]);
				}
				t = t2;
			}
		}
		remainLength = segLength - segLength*t;
		if (this.isLine){
			if (remainLength > this.onLength){
				t2 = t + ont;
				c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
				this.targetCurveTo(c[2], c[3], c[4], c[5]);
				this.targetMoveTo(x, y);
				this.overflow = this.offLength-(remainLength-this.onLength);
				this.isLine = false;
			}else{
				c = this.curveSliceFrom(sx, sy, cx, cy, x, y, t);
				this.targetCurveTo(c[2], c[3], c[4], c[5]);
				if (segLength == this.onLength){
					this.overflow = 0;
					this.isLine = !this.isLine;
				}else{
					this.overflow = this.onLength-remainLength;
					this.targetMoveTo(x, y);
				}
			}
		}else{
			if (remainLength > this.offLength){
				t2 = t + offt;
				c = this.curveSlice(sx, sy, cx, cy, x, y, t, t2);
				this.targetMoveTo(c[4], c[5]);
				c = this.curveSliceFrom(sx, sy, cx, cy, x, y, t2);
				this.targetCurveTo(c[2], c[3], c[4], c[5]);
				
				this.overflow = this.onLength-(remainLength-this.offLength);
				this.isLine = true;
			}else{
				this.targetMoveTo(x, y);
				if (remainLength == this.offLength){
					this.overflow = 0;
					this.isLine = !this.isLine;
				}else this.overflow = this.offLength-remainLength;
			}
		}
	}
	
	// direct translations
	/**
	 * Clears the drawing in target
	 * @return nothing
	 */
	public function clear(Void):Void {
		this.target.clear();
	}
	/**
	 * Sets the lineStyle for target
	 * @param thickness An integer that indicates the thickness of the line in points; valid values
	 * are 0 to 255. If a number is not specified, or if the parameter is undefined, a line is not
	 * drawn. If a value of less than 0 is passed, Flash uses 0. The value 0 indicates hairline
	 * thickness; the maximum thickness is 255. If a value greater than 255 is passed, the Flash
	 * interpreter uses 255.
	 * @param rgb A hex color value (for example, red is 0xFF0000, blue is 0x0000FF, and so on) of
	 * the line. If a value isn’t indicated, Flash uses 0x000000 (black).
	 * @param alpha An integer that indicates the alpha value of the line’s color; valid values are
	 * 0–100. If a value isn’t indicated, Flash uses 100 (solid). If the value is less than 0, Flash
	 * uses 0; if the value is greater than 100, Flash uses 100.
	 * @return nothing
	 */
	public function lineStyle(thickness:Number,rgb:Number,alpha:Number):Void {
		this.target.lineStyle(thickness,rgb,alpha);
	}
	/**
	 * Sets a basic fill style for target
	 * @param rgb A hex color value (for example, red is 0xFF0000, blue is 0x0000FF, and so on). If
	 * this value is not provided or is undefined, a fill is not created.
	 * @param alpha An integer between 0–100 that specifies the alpha value of the fill. If this value
	 * is not provided, 100 (solid) is used. If the value is less than 0, Flash uses 0. If the value is
	 * greater than 100, Flash uses 100.
	 * @return nothing
	 */
	public function beginFill(rgb:Number,alpha:Number):Void {
		this.target.beginFill(rgb,alpha);
	}
	/**
	 * Sets a gradient fill style for target
	 * @param fillType Either the string "linear" or the string "radial".
	 * @param colors An array of RGB hex color values to be used in the gradient (for example, red is
	 * 0xFF0000, blue is 0x0000FF, and so on).
	 * @param alphas An array of alpha values for the corresponding colors in the colors array; valid
	 * values are 0–100. If the value is less than 0, Flash uses 0. If the value is greater than 100,
	 * Flash uses 100.
	 * @param ratios An array of color distribution ratios; valid values are 0–255. This value defines
	 * the percentage of the width where the color is sampled at 100 percent.
	 * @param matrix A transformation matrix that is an object with one of two sets of properties.
	 * @return nothing
	 */
	public function beginGradientFill(fillType:String,colors:Array,alphas:Array,ratios:Array,matrix:Object):Void {
		this.target.beginGradientFill(fillType,colors,alphas,ratios,matrix);
	}
	/**
	 * Ends the fill style for target
	 * @return nothing
	 */
	public function endFill(Void):Void {
		this.target.endFill();
	}
	// private methods
	private function lineLength(sx:Number, sy:Number, ex:Number, ey:Number):Number {
		if (arguments.length == 2){
			return Math.sqrt(sx*sx + sy*sy);
		}
		var dx = ex - sx;
		var dy = ey - sy;
		return Math.sqrt(dx*dx + dy*dy);
	}
	private function curveLength(sx:Number, sy:Number, cx:Number, cy:Number, ex:Number, ey:Number, accuracy:Number):Number {
		var total:Number = 0;
		var tx:Number = sx;
		var ty:Number = sy;
		var px:Number, py:Number, t:Number, it:Number, a:Number, b:Number, c:Number;
		var n:Number = (accuracy) ? accuracy : this._curveaccuracy;
		for (var i:Number = 1; i<=n; i++){
			t = i/n;
			it = 1-t;
			a = it*it; b = 2*t*it; c = t*t;
			px = a*sx + b*cx + c*ex;
			py = a*sy + b*cy + c*ey;
			total += this.lineLength(tx, ty, px, py);
			tx = px;
			ty = py;
		}
		return total;
	}
	private function curveSlice(sx:Number, sy:Number, cx:Number, cy:Number, ex:Number, ey:Number, t1:Number, t2:Number):Array {
		if (t1 == 0){
			return this.curveSliceUpTo(sx, sy, cx, cy, ex, ey, t2);
		}
		else{
			if (t2 == 1){
				return this.curveSliceFrom(sx, sy, cx, cy, ex, ey, t1);
			}
		}
		var c = this.curveSliceUpTo(sx, sy, cx, cy, ex, ey, t2);
		c.push(t1/t2);
		return this.curveSliceFrom.apply(this, c);
	}
	private function curveSliceUpTo(sx:Number, sy:Number, cx:Number, cy:Number, ex:Number, ey:Number, t:Number):Array {
		if (t == undefined){
			t = 1;
		}
		if (t != 1) {
			var midx:Number = cx + (ex-cx)*t;
			var midy:Number = cy + (ey-cy)*t;
			cx = sx + (cx-sx)*t;
			cy = sy + (cy-sy)*t;
			ex = cx + (midx-cx)*t;
			ey = cy + (midy-cy)*t;
		}
		return [sx, sy, cx, cy, ex, ey];
	}
	private function curveSliceFrom(sx:Number, sy:Number, cx:Number, cy:Number, ex:Number, ey:Number, t:Number):Array {
		if (t == undefined){
			t = 1;
		}
		if (t != 1) {
			var midx:Number = sx + (cx-sx)*t;
			var midy:Number = sy + (cy-sy)*t;
			cx = cx + (ex-cx)*t;
			cy = cy + (ey-cy)*t;
			sx = midx + (cx-midx)*t;
			sy = midy + (cy-midy)*t;
		}
		return [sx, sy, cx, cy, ex, ey];
	}
	private function targetMoveTo(x:Number, y:Number):Void {
		this.pen = {x:x, y:y};
		this.target.moveTo(x, y);
	}
	private function targetLineTo(x:Number, y:Number):Void {
		if (x == this.pen.x && y == this.pen.y) return;
		this.pen = {x:x, y:y};
		this.target.lineTo(x, y);
	}
	private function targetCurveTo(cx:Number, cy:Number, x:Number, y:Number):Void {
		if (cx == x && cy == y && x == this.pen.x && y == this.pen.y) return;
		this.pen = {x:x, y:y};
		this.target.curveTo(cx, cy, x, y);
	}
}
