// three characters that are unlikely to occur in succession
var SEPARATOR = '(|]';

var RESPONSE_KEY_SCRIPT = '::SCRIPT::';

function PopulateForm(text)
{
    PopulateFormInternal(text, false);
}

function PopulateFormAndChange(text)
{
    PopulateFormInternal(text, true);
}

// text is a string of the form
// elementId1=data1(|]elementId2=data2...
function PopulateFormInternal(text, fireChangeEvent)
{
    var data = text.split(SEPARATOR);
    for (var i = 0; i < data.length; ++i)
    {
        // find the equal sign
        var pos = data[i].indexOf('=');
        if (pos < 0) continue;
        
        // get the element
        var element = document.getElementById(data[i].substring(0, pos));
        if (element == null) continue;
        
        // insert the value into the page
        var value = pos < data[i].length - 1 ? data[i].substring(pos + 1) : '';
        var tag = element.tagName.toLowerCase();
        var type = element.type != undefined ? element.type.toLowerCase() : null;
        
        if (tag == 'input' && (type == 'checkbox' || type == 'radio'))
        {
            element.checked = Boolean(value);
            if (fireChangeEvent && element.onchange != null)
                element.onchange();
        }
        else if (tag == 'input' || tag == 'textarea')
        {
            element.value = value;
            if (fireChangeEvent && element.onchange != null)
                element.onchange();
        }
        else if (tag == 'select')
        {
            for (var j = 0; j < element.options.length; ++j)
            {
                if (element.options[j].value == value)
                {
                    element.selectedIndex = j;
                    if (fireChangeEvent && element.onchange != null)
                        element.onchange();
                    break;
                }
            }
        }
        else if (tag == 'a')
            element.href = value;
        else
            element.innerHTML = value;
    }
}

function PopulateFormAdvanced(text)
{
    PopulateFormAdvancedInternal(text, false);
}

function PopulateFormAdvancedAndChange(text)
{
    PopulateFormAdvancedInternal(text, true);
}

function PopulateFormAdvancedInternal(text, fireChangeEvent)
{
    var data = text.split(SEPARATOR);
    var funcs = [];
    for (var i = 0; i < data.length; ++i)
    {
        // find the equal sign
        var pos = data[i].indexOf('=');
        if (pos < 0) continue;
        
        // get the value
        var value = pos < data[i].length - 1 ? data[i].substring(pos + 1) : '';
        
        // the period (.) delimits member and subsequent accessors
        var nameParts = data[i].substring(0, pos).split('.');
        if (nameParts.length < 2)
        {
            // save the value as a script to run
            if (value.length > 0 && nameParts[0] == RESPONSE_KEY_SCRIPT)
                funcs[funcs.length] = value;
            continue;
        }
        
        // try to get the element
        var element = document.getElementById(nameParts[0]);
        if (element == null) continue;
        
        // get info about the element to set
        var tag = element.tagName.toLowerCase();
        var type = element.type != undefined ? element.type.toLowerCase() : null;
        var fieldName = nameParts[nameParts.length - 1].toLowerCase();
        
        // set the value
        if (tag == 'select' && fieldName == 'value')
        {
            // handle select elements differently
            for (var j = 0; j < element.options.length; ++j)
            {
                if (element.options[j].value == value)
                {
                    element.selectedIndex = j;
                    break;
                }
            }
        }
        else
        {
            // if the 'checked' field is being set, convert the value to a boolean
            if (fieldName == 'checked')
                value = Boolean(value);
                
            // keep going until we get to the property to change
            var field = element;
            for (var j = 1; j < nameParts.length - 1; ++j)
                field = field[nameParts[j]];
                
            // set the value
            field[nameParts[nameParts.length - 1]] = value;
        }
        
        // fire the onchange event
        if (fireChangeEvent && element.onchange != null
            && (fieldName == 'checked' || fieldName == 'value'))
            element.onchange();
    }
    
    // run the scripts
    for (var f = 0; f < funcs.length; ++f)
    {
        var func = new Function(funcs[f]);
        func();
    }
}

function GetRequestParams(formId)
{
    var pairs = [];
    var pairCount = 0;
    var form = document.getElementById(formId);
    var elementCount = form.elements.length;
    
    for (var i = 0; i < elementCount; ++i)
    {
        element = form.elements[i];
        var tagName = element.tagName.toUpperCase();
        var type = element.type != undefined ? element.type.toUpperCase() : '';
        
        if (tagName == 'SELECT')
        {
            // go through the element's options and add selected options
            var optionCount = element.options.length;
            for (var o = 0; o < optionCount; ++o)
            {
                if (element.options[o].selected)
                    pairs[pairCount++] = [element.name, element.options[o].value];
            }
        }
        else if (tagName == 'TEXTAREA')
        {
            // ge thte textarea's value'
            pairs[pairCount++] = [element.name, element.value];
        }
        else if (tagName == 'INPUT')
        {
            if (type == 'RADIO' || type == 'CHECKBOX')
            {
                if (element.checked)
                {
                    // handle a special case of radios and checkboxes without values
                    if (!element.value)
                        pairs[pairCount++] = [element.name, 'on'];
                    else
                        pairs[pairCount++] = [element.name, element.value];
                }
            }
            else
            {
                // get the input's value
                pairs[pairCount++] = [element.name, element.value];
            }
        }
    }
    return pairs;
}

function GetMultipleRequestParams()
{
    var count = arguments.length;
    var params = [];
    for (var i = 0; i < count; ++i)
        params = params.concat(GetRequestParams(arguments[i]));
    return params;
}

// text is a string of IDs concatenated with ampersands
// TODO we can send a message by like this: msg=Here's a message.(|]ids=element1&element2...
function HideElements(text)
{
    var ids = text.split('&');
    for (var i = 0; i < ids.length; ++i)
    {
        var element = document.getElementById(ids[i]);
        if (element == null) continue;
        element.style['display'] = 'none';
    }
    
    if (ids.length == 0)
        alert('You cannot delete that item.');
}

// params is an object
function BuildParamString(params)
{
    var i = 0;
    var paramArray = [];
    for (var j = 0; j < params.length; ++j)
    {
        if (params[j].length == 2)
            paramArray[i++] = encodeURIComponent(params[j][0]) + '=' + encodeURIComponent(params[j][1]);
    }
    return paramArray.join('&');
}

function AjaxGet(url, params, callback, progressUpdateIdKey, progressUpdateId)
{
    // progress update
    if (progressUpdateIdKey != undefined && progressUpdateId != undefined)
    {
        params[params.length] = [progressUpdateIdKey, progressUpdateId];
        var progress = document.getElementById(progressUpdateId);
        if (progress != null)
            progress.style.visibility = 'visible';
    }
    // add the parameter so the request is asynchronous
    params[params.length] = ['async', 1];
    // send the request
    var request = new XMLHttpRequest();
    request.open('GET', url + '?' + BuildParamString(params), true);
    request.onreadystatechange = function() {
      if (request.readyState === 4 && request.status === 200)
          callback(request.responseText);
    }
    request.send(null);
}

function AjaxPost(url, params, callback, progressUpdateIdKey, progressUpdateId)
{
    // progress update
    if (progressUpdateIdKey != undefined && progressUpdateId != undefined)
    {
        params[params.length] = [progressUpdateIdKey, progressUpdateId];
        var progress = document.getElementById(progressUpdateId);
        if (progress != null)
            progress.style.visibility = 'visible';
    }
    // add the parameter so the request is asynchronous
    params[params.length] = ['async', 1];
    // send the request
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    request.onreadystatechange = function() {
      if (request.readyState === 4 && request.status === 200)
          callback(request.responseText);
    }
    request.send(BuildParamString(params));
}

// this isn't used for anything, but i wrote it, so why not keep it?
// str is the string to search in
// data is a map of keys to data
function DataReplace(str, data) {
  return str.replace(/{([\w\d]+)}/g, function(match, name) { 
    return typeof data[name] != 'undefined'
      ? data[name]
      : match
    ;
  });
};
