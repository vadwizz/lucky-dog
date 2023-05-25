jQuery(document).ready(function($) {
    

  console.log('hello this fucking world');

 /* $.each($('.form-item'), function(){
    if($(this).find('input').prop('checked') == true){
        $(this).addClass('active');
        console.log('12354568');
    }
  })
  $(document).on('click', '.form-item', function(){
    $(this).parents('.fieldset-wrapper > div').find('.form-input').removeClass('active');
    $(this).parents('.fieldset-wrapper > div').find('form-input input').prop('checked', false);
    $(this).toggleClass('active');
    $(this).find('input').prop('checked', true);
    return false
  })
*/
  

//
//
//
//
//ціна і знижки

let price = $('.field--name-price').first().text();
price = parseFloat(price.replace(/^\s*\n/gm, '').replace('UAH', ''));

let realPrice = $('.field--name-price').last().text();
realPrice = parseFloat(realPrice.replace(/^\s*\n/gm, '').replace('UAH', ''));

console.log(price);
console.log(realPrice);

if(price === realPrice){
  $('.field--name-price').first().toggleClass('notPromo');
} else {
  $('.field--name-price').first().toggleClass('promo');
}


let viewsPrice = $('.views-field-field-price > .field-content').text();
viewsPrice = parseFloat(viewsPrice.replace(/^\s*\n/gm, '').replace('UAH', ''));
console.log(viewsPrice);




$('.expanded.dropdown').mouseenter(function () { 

  $(this).addClass('open');

});

$('.expanded.dropdown').mouseleave(function () { 

  $(this).removeClass('open');
  
});




let image1 = $('#views-bootstrap-block-image-block-1 > div > div:nth-child(1) > div > span > img');
$(image1).click(function (e) { 
  window.location.href= "/cat"
});

let image2 = $('#views-bootstrap-block-image-block-1 > div > div:nth-child(2) > div > span > img');
$(image2).click(function (e) { 
  window.location.href= "/dog"
});

let image3 = $('#views-bootstrap-block-image-block-1 > div > div:nth-child(3) > div > span > img');
$(image3).click(function (e) { 
  window.location.href= "/parrot"
});

let image4 = $('#views-bootstrap-block-image-block-1 > div > div:nth-child(4) > div > span > img');
$(image4).click(function (e) { 
  window.location.href= "/rodent"
});

let image5 = $('#views-bootstrap-block-image-block-1 > div > div:nth-child(5) > div > span > img');
$(image5).click(function (e) { 
  window.location.href= "/fish"
});

});