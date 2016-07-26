# Difference from the original code

There is a change in the function `embed()`. The regular code from https://github.com/jonathantneal/svg4everybody/blob/master/dist/svg4everybody.js reads: 
    
    // append the fragment into the svg
    svg.appendChild(fragment);
    
This has been changed to:

    var group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
    group.setAttribute('class', 'use');
    group.appendChild(fragment);
    svg.appendChild(group);
    
*svg4everybody* originally removes the `<use>` element and replaces it with the relevant SVG. By wrapping the SVG in a group with the class `use` cross browser CSS manipulation can be maintained by using the selector `svg use, svg .use`.
    