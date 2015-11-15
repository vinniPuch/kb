
$().ready(function(){
    
    var file_manager = fileManager;
    
    file_manager.init();
    
});


fileManager = {
    
    controls: "<td class='controls' contenteditable = 'false'>\n\
                    <img class='controls-add' src='/img/add.png' /> \n\
                    <img class='controls-delete' src='/img/delete.png' /> \n\
               </td> ",
       
       
    init:function(){

        $("#save-button").click(function(e){
            
            e.preventDefault();
            fileManager.save();
            
        });
        
        $("#download-button").click(function(){
                
            fileManager.save( download = true );
            
        });
        
        $("#cancel-button").click(function(){
            
            fileManager.cancel();
            
        });
        
        $("iframe#content-frame").load(function(){
        
            fileManager.addTabelControls();
            fileManager.doEditable();
            
        });
                
    },
    
    
    save:function( download ) {
        
       //prepare document to save
       
       fileManager.beforeSave();
       
        $.ajax({
                type: "POST",
                url: "/?action=save",
                data: { 
                        document:$("iframe#content-frame").contents().find("html").html(),
                        download: download
                
                        }
        })
        .done(function( data ) {

            console.log(data);
            
            if( data !== 'true' ) {
                
                alert('File Not save');
        
            } else{
                
                if( download == true){
                    
                    window.location = '/?action=download';
                    
                }else{
                    
                    $('#msg-area').html("File save success.");
                    
                    setTimeout(function(){
                        
                        $('#msg-area').html("");
                        
                    }, 1500);
                    
                }
                
            }
            return data;            
        });
        
    },
            
            
    beforeSave:function(){

        $("iframe#content-frame").contents().find("td").removeAttr('contenteditable');
        $("iframe#content-frame").contents().find("td").removeClass('active');
        
        $("iframe#content-frame").contents().find(".editable").removeAttr('contenteditable');
        $("iframe#content-frame").contents().find(".editable").removeClass('active');
                        
    },
            
            
    cancel:function() {
        
        if( confirm("You really want cancel all changes") ) {
            window.location = '/?action=close';
        }
        
    },

            
    doEditable:function() {
        
        $("iframe#content-frame").contents().find("body").click(function(e){
           
           // editable logic for page sub title          
                     
           if( $(e.target).hasClass('editable') ) {
               $(e.target).attr('contenteditable', "true").addClass("active");
               
               var active = $(e.target);
               
               $(active).mouseout(function(){
                   $(active).removeAttr('contenteditable').removeClass("active");
                   $(active).unbind('mouseout');
               });
               
           }
           
           // editable logic for table
           
           if( $(e.target).is('td:not(.controls)') ) {
               
               var clicked_table_cell = $(e.target);
               var active_table_cell = $("iframe#content-frame").contents().find('td.active');
              
               if( active_table_cell.get(0) !== clicked_table_cell.get(0) ){

                  $("iframe#content-frame").contents().find('td.active').attr('contenteditable', "false");
                  $("iframe#content-frame").contents().find('td.active').removeClass('active');
                  
                  clicked_table_cell.addClass('active');
                  clicked_table_cell.attr('contenteditable', "true");

               }
           }
           
           // add new row to table  
           
           if( $(e.target).hasClass('controls-add') ) {
               fileManager.addRow(e);
           }
           
           // delete row from table 
           
           if( $(e.target).hasClass('controls-delete') ) {
               
               if( confirm("You really want delete row?")) {
                   fileManager.deleteRow(e);
               }
               
           }
           
        });

    },
            
    
    addTabelControls:function() {

        $("iframe#content-frame").contents().find("tr").each(function() {

            $(this).append(fileManager.controls);
            
        });
       
    },
    
    
    addRow:function(e) {

        var clicked_table_row = $(e.target).parent().parent();
        
        var pattern = clicked_table_row.clone().addClass("inserted");
        
        clicked_table_row.after(pattern);
        
        $("iframe#content-frame").contents().find(".inserted > td:not(.controls)").each(function(){
            
            $(this).html('').removeClass("active");
            
        });
        
        $("iframe#content-frame").contents().find(".inserted").removeClass("inserted");
       
        
    },
            
    
    deleteRow:function(e) {

        var clicked_table_row = $(e.target).parent().parent();
        clicked_table_row.remove();
        
    }
    
}