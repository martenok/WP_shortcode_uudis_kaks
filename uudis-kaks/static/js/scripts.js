
function korja() {
  // Salvestab kaardil oleva uudise WP postituseks
  // Tegelikult saadab sisu tagasi serverisse, kus siis uudis_kaks_create() selle ära salvestab

  var kaart = jQuery(event.target).parent();
  var andmed = JSON.parse(jQuery(kaart).find(".u2-andmed").attr('data-u2'));
  // var nupp = jQuery(event.target);

  // var id = jQuery(kaart).attr('id');
  // var kuup = jQuery(kaart).find( "kuup_" + id).innerText;

  var voog = andmed.link;
  // var nupp_id = "";
  var kategooria = andmed.news_category;

  console.log(andmed);


  var sisu = jQuery(kaart).find(".modal-body").eq(0).html();
  var pealkiri = jQuery(kaart).find("h2").eq(0).text();
  var params =  {
    action : 'uudis_kaks_create',
    'post_title': pealkiri,
    'post_content'  : sisu,
    'post_status'   : 'publish',
    'post_author'   : 1,
    'post_category' : kategooria,
    'lead_image'    : andmed.lead_image
  };

  console.log(kaart);
  // console.log(kuup);
  // console.log(wurl);
  console.log(voog);
  console.log(kategooria);
  console.log(sisu);
  console.log(pealkiri);

  // var ajaxurl = uudis_kaks_params.uudis_kaks_ajax_url;
  jQuery.ajax({
    url: uudis_kaks_params.uudis_kaks_ajax_url,
    type: "POST",
    data: params,
    dataType: 'json',
    // success: function(response) {
    //         console.log(response);
    //     }
  }).done(function(json) {
					if(!json.success) {
						console.log(json);
					}
					if(json.data) {
            // console.log(jQuery(kaart).find(".btn btn-success btn-sm"));
            // jQuery(kaart).find(".btn btn-success btn-sm").switchClass( ".btn btn-success btn-sm", "btn btn-danger btn-sm", 1000, "easeInOutQuad" ).attr("onclick","kustuta()");
            jQuery(kaart).find("button[name='nupp']").attr("onclick","kustuta("+ json.data + ")").removeClass( "btn btn-success btn-sm" ).addClass( "btn btn-danger btn-sm" );
            // jQuery(kaart).find(":button").replaceWith( "<button type='submit' onclick=kustuta() class='btn btn-danger btn-sm'></button>" );
						// events = prepareEventSources(json.data);
            console.log(json.data);
					}
				});

}

function kustuta(post_id){
  var kaart = jQuery(event.target).parent();
  var id = jQuery(kaart).attr('id');

  console.log(kaart);

  var params =  {
    action : 'uudis_kaks_kustuta',
    'post_id': post_id
  };

  jQuery.ajax({
    url: uudis_kaks_params.uudis_kaks_ajax_url,
    type: "POST",
    data: params,
    dataType: 'json'

    }).done(function(json) {
					if(!json.success) {
						console.log(json);
					}
					if(json.data) {
            jQuery(kaart).find("button[name='nupp']").attr("onclick","korja(" + id + ")").removeClass( "btn btn-danger btn-sm" ).addClass( "btn btn-success btn-sm" );
					}
				});
  // console.log("Ahaa");
}

function tulevane()
{
  // var kaart = jQuery(event.target).parentsUntil(jQuery(".u2-card")).parent();
  var kaart = jQuery(event.target).parent();
  var andmed = JSON.parse(jQuery(kaart).find(".u2-andmed").attr('data-u2'));
  var voo_nimi = andmed.voo_nimi;

  var kaardid = jQuery("div[name=" + voo_nimi + "]") ;

  if (jQuery(event.target).attr('name') === 'tulevane') {
    var mis_filter = ':last';
  } else if (jQuery(event.target).attr('name') === 'eelmine') {
    var mis_filter = ':first';
  }

  andmed = JSON.parse(jQuery( kaardid ).filter(mis_filter).find(".u2-andmed").attr('data-u2'));
  var lk = parseInt(andmed.lk);
  var lk_kokku = parseInt(andmed.lk_kokku);

  console.log("Nupu nimi:", jQuery(event.target).attr('name'), "+ lk:", lk, "+ lk kokku:", lk_kokku);

  var samm  = 0;

  if (jQuery(event.target).attr('name') === 'tulevane') {
    if (jQuery(kaardid).length > 1){
      samm = (jQuery(kaardid).length - 1) < (lk_kokku - lk) ? jQuery(kaardid).length - 1 : lk_kokku - lk;
    } else if (lk == lk_kokku) {
      samm = 0;
      console.log("tulevane samm:", samm);
    } else { samm = 1 ;}
  } else if (jQuery(event.target).attr('name') === 'eelmine') {
    if (jQuery(kaardid).length > 1){
      samm = (1 - jQuery(kaardid).length) > (1 - lk) ? 1 - jQuery(kaardid).length : 1 - lk;
    } else if (lk == 1) {
      samm = 0;
      console.log("eelmine samm:", samm);
    }
    else { var samm = - 1 ;}
  }

  console.log(kaardid);
  console.log("Samm: ", samm);

jQuery(kaardid).each(function(){
  //  kaart = jQuery(this);
   andmed = JSON.parse(jQuery(this).find(".u2-andmed").attr('data-u2'));
   andmed.lk = parseInt(andmed.lk) + samm;

   console.log("ID:", andmed.id);
   console.log("lk:", andmed.lk);

   var params = {
     action : 'uudis_kaks_mercy'
   };

  params = jQuery.extend(params, andmed);
  console.log("Params ID:", params.id, "Params lk:", params.lk);

  jQuery.ajax({
    url: uudis_kaks_params.uudis_kaks_ajax_url,
    type: "POST",
    data: params,
    dataType: 'json'

    }).done(function(json) {
					if(!json.success) {
						console.log(json);
					}
					if(json.data) {
            // jQuery(this).replaceWith(json.data);
            console.log("Mercy ID:", json.data.data.id);
            jQuery("#" + json.data.data.id).html(json.data.content);
            // console.log(json.data.content);
					}
				});
  })

  // var id = jQuery(kaart).attr('id');
  // console.log(kaardid);

  // var kloon = jQuery(  kaardid ).filter(":last").clone(true);

  // jQuery( kaardid ).filter(":first").replaceWith(kloon);
}

function eelmine(){


}
