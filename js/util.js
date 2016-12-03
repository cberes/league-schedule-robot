String.prototype.trim = function()
{
    return this.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
};

function isPlaceholderSupported() {
    test = document.createElement('input');
    return ('placeholder' in test);
}

function stopDefaultAction(evt)
{
    if (evt.preventDefault)
        evt.preventDefault();
    else
        evt.returnValue = false;
}

function disableDefaultAction(id, evt)
{
    document.getElementById(id).addEventListener(evt, stopDefaultAction, false);
}

function onSubmitPasswordRequired(popupId, hiddenId, executeOnSubmit)
{
    // show the popup
    var popup = document.getElementById(popupId);
    popup.style.display = 'block';
    
    // put the ID of the form to process in a hidden field
    var hidden = document.getElementById(hiddenId);
    hidden.value = executeOnSubmit;
}

function onPasswordTestSubmit(popupId, hiddenId, formId)
{
    // close the popup
    var popup = document.getElementById(popupId);
    popup.style.display = 'none';
    
    // get the hidden field, which has the function to call when submitted the popup's form
    var hidden = document.getElementById(hiddenId);
    
    // call the ajax function 
    var f = new Function(hidden.value);
    f();
   
    // clear the form (needs to be done last)
    if (formId)
    {
        var form = document.getElementById(formId);
        if (form) form.reset();
    }
}

function onClearButtonClick(formId, buttonId, buttonValue)
{
    // clear the elements in the form
    var elements = document.getElementById(formId).elements;
    for (var i = 0; i < elements.length; ++i)
    {
        var tag = elements[i].tagName.toLowerCase();
        var type = elements[i].type.toLowerCase();
        var classNames = getClassNames(elements[i]);
        if (tag == 'input' && type == 'submit')
            continue;
        else if (tag == 'input' && (type == 'checkbox' || type == 'radio'))
            elements[i].checked = false;
        else if (tag == 'input' || tag == 'textarea')
            elements[i].value = '';
        else if (tag == 'select')
            elements[i].selectedIndex = 0;
        else if (classNames.indexOf('clear') > -1)
        {
            if (tag == 'a')
                elements[i].href = '#';
        }
    }
    // reset a button value
    var button = document.getElementById(buttonId);
    if (button != null)
        button.value = buttonValue;
}

function onCopyTextLinkClick(link)
{
    link.select();
}

function getClassNames(element)
{
    return element.className.split(' ');
}

function closePopup(element, className, formId)
{
    while (element != null && element != undefined && getClassNames(element).indexOf(className) == -1)
        element = element.parentNode;
    if (element != null && element != undefined)
        element.style.display = 'none';
   
    // clear the form 
    if (formId)
    {
        var form = document.getElementById(formId);
        if (form) form.reset();
    }
}

function onSingletonCheckboxGroupClick(checkbox)
{
    if (!checkbox.checked) return;
    var boxes = document.getElementsByName(checkbox.name);
    for (var i = 0; i < boxes.length; ++i)
    {
        boxes[i].checked = boxes[i] == checkbox;
        if (boxes[i].onchange)
            boxes[i].onchange(boxes[i]);
    }
}

function selectText(element)
{
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(element);
        range.select();
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(element);
        window.getSelection().addRange(range);
    }
}

var FadeEffect = function()
{
    return { // the bracket needs to be on the same line as return?
        init:function(id, flag, target, delay)
        {
            this.elem = document.getElementById(id);
            clearInterval(this.elem.si);
            this.interval = 20; // 20 ms
            this.target = target ? target : flag ? 100 : 0;
            this.flag = flag || -1;
            this.alpha = this.elem.style.opacity ? parseFloat(this.elem.style.opacity) * 100 : 0;
            if (delay)
                setTimeout(function() { FadeEffect.tween() }, delay);
            else
                setTimeout(function() { FadeEffect.tween() }, this.interval);
        },
        tween:function()
        {
            if (this.alpha != this.target)
            {
                setTimeout(function() { FadeEffect.tween() }, this.interval);
                var value = Math.round(this.alpha + ((this.target - this.alpha) * .05)) + (1 * this.flag);
                this.elem.style.opacity = value / 100;
                this.elem.style.filter = 'alpha(opacity=' + value + ')';
                this.alpha = value
            }
            else if (this.target == 0)
                this.elem.style.display = 'none';
        }
    }
}();