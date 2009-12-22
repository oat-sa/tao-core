/**

	Frédéric Saunier
	open-sourcing@tekool.net
	http://www.tekool.net/

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	You can also try : http://www.gnu.org/copyleft/gpl.html

**/

class net.tekool.utils.Relegate extends Object
{
	static function create(t:Object,f:Function):Function
	{
		var a:Array = arguments.splice(2);
		return function()
		{
			return f.apply
			(
				t,
				arguments.concat(a)
			);
		};
	}
}