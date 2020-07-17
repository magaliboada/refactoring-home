var $collectionHolder;

var $addNewItem = $('<div href="#" class="btn btn-info">Add new Item</div>');


$(document).ready( function() {


    $(".new input#room_Image").prop('required',true);

    $('.existing .item .item-link').prop('disabled', true);

    $( window ).resize(function() {
        placeButton();
    });

    //get the collectionHolder
    $collectionHolder = $('#item-list.edit');

    //Append Add Button to the CollectionHolder
    $collectionHolder.append($addNewItem);

    $collectionHolder.data('index', $collectionHolder.find('.panel.edit').length)

    //Add Remove button to existing Items
    $collectionHolder.find('.panel.edit').each( function () {
        addRemoveButton($(this));
    });

    //Handle event for addNewItem
    $addNewItem.click( function(){
        //create new form and append it to the collectionholder
        addNewForm();
        placeButton();
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
    var $panel = $('<div class="panel panel-warning item shadow pt-4 pl-4 pr-4 mb-4 bg-white"><div class="panel-heading">New Item</div></div>');

    //Create the panel-body and append the form 
    var $panelBody = $('<div class="panel-body main-information"></div>').append(newForm);


    //Append the form to the panel
    $panel.append($panelBody);

    //Append panel to the CollectionHolder
    
    $addNewItem.before($panel);


    //Apend Remove button to the new panel
    addRemoveButton($panel);
}

//Remove Items Forms

function addRemoveButton($panel) {

    placeButton();

    //remove button
    var $removeButton = $('<div class="btn btn-danger m-4">Remove Item</div>');
    var $panelFooter = $('<div class="panel-footer"></div>').append($removeButton);

    //handle Click Event

    $removeButton.click( function(e) {
        // console.log(e.target.parent());
        $(e.target).parents('.item').remove();
            placeButton();

    });

    
    //append foote to banner
    $panel.append($panelFooter);

}

//Place buttons bellow form
function placeButton() {
    var buttonHeight = $('.edit-form .content').height() + $('.header').height() ;
    $('#room_save').css('top', buttonHeight);
    $('.edit.btn-warning').css('top', buttonHeight);
    $('.btn-danger.delete').css('top', buttonHeight);

    var buttonWidth = $('.edit-form .content').width();

    var windowMargin = $( window ).width() - $('.edit-form .content').width();
    windowMargin /= 2;

    if($('h1')[0].innerText != 'New Room') {
        buttonWidth = $('.edit-form .content').width() - $('#room_save').width() -  $('.edit.btn-warning').width() - $('.btn-danger.delete').width();

    } else {
        buttonWidth = $('.edit-form .content').width() - $('#room_save').width() -  $('.edit.btn-warning').width();
        
    }
    
    buttonWidth /= 5;

    leftValue = windowMargin + buttonWidth;
    $('#room_save').css('left', leftValue);

    leftValue += $('#room_save').width() + buttonWidth;
    $('.edit.btn-warning').css('left', leftValue);

    leftValue += $('.edit.btn-warning').width() + buttonWidth;
    $('.btn-danger.delete').css('left', leftValue);
    
}