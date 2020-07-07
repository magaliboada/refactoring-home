var $collectionHolder;

var $addNewItem = $('<div href="#" class="btn btn-info">Add new Item</div>');

$(document).ready( function() {

    //get the collectionHolder
    $collectionHolder = $('#item-list');

    //Append Add Button to the CollectionHolder
    $collectionHolder.append($addNewItem);

    $collectionHolder.data('index', $collectionHolder.find('.panel').length)

    //Add Remove button to existing Items
    $collectionHolder.find('.panel').each( function () {
        addRemoveButton($(this));
    });

    //Handle event for addNewItem
    $addNewItem.click( function(){
        //create new form and append it to the collectionholder
        addNewForm();
        

    });
});

//Add New Items Forms

function addNewForm() {
    //Get the prototype
    var prototype = $collectionHolder.data('prototype');

    //Get the index
    var index = $collectionHolder.data('index');

    //Vreate the form
    var newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);

    $collectionHolder.data('index', index+1);

    //Create the panel
    var $panel = $('<div class="panel item panel-warning"><div class="panel-heading"></div></div>');

    //Create the panel-boya dna append the form 
    var $panelBody = $('<div class="panel-heading"></div>').append(newForm);

    //Append the form to the panel
    $panel.append($panelBody);

    //Append panel to the CollectionHolder
    $addNewItem.before($panel);


    //Apend Remove button to the new panel
    addRemoveButton($panel);
}

//Remove Items Forms

function addRemoveButton($panel) {
    //remove button
    var $removeButton = $('<div class="btn btn-danger">Remove Item</div>');
    var $panelFooter = $('<div class="panel-footer"></div>').append($removeButton);

    //handle Click Event

    $removeButton.click( function(e) {
        // console.log(e.target.parent());
        $(e.target).parents('.item').slideUp(1000, function() {
            $(this).remove();

        });

    });

    //append foote to banner
    $panel.append($panelFooter);

}