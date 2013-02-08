//this script loads google map and show it on the page
$(function() {
    
function showMap()
  {
      var map;
      var latlng = new google.maps.LatLng($('#lat').val(), $('#lon').val());
      
      if(document.getElementById("googleMap"))
       {
          map = new google.maps.Map(document.getElementById("googleMap"), {
             zoom: 14,
             center: latlng,
             mapTypeId: google.maps.MapTypeId.ROADMAP
          });
          var marker = new google.maps.Marker({
              position: latlng,
              map: map,
              title: $('#markerTitle').val(),       
          });
       }
  }

 $('#btndialog').click(function() {  
     $('#dialogContent').css('display', 'inline');
       $("#myDialog").dialog({
            width: 500,
            modal: true
        });
    });
    
   showMap(); 
   
});