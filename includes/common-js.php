<!-- jQuery 2.1.4 -->
    <script src="/<?php echo $host_name; ?>/plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="/<?php echo $host_name; ?>/js/jquery-ui.min.js" type="text/javascript"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script type="text/javascript">
      //$.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="/<?php echo $host_name; ?>/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

	<!-- iCheck -->
    <script src="/<?php echo $host_name; ?>/plugins/iCheck/icheck.js" type="text/javascript"></script>

	<!-- Select2 -->
    <script src="/<?php echo $host_name; ?>/plugins/select2/select2.full.min.js" type="text/javascript"></script>

    <!-- InputMask -->
    <script src="/<?php echo $host_name; ?>/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="/<?php echo $host_name; ?>/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="/<?php echo $host_name; ?>/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>

    <!-- Morris.js charts -->
    <script src="/<?php echo $host_name; ?>/js/raphael-min.js"></script>
    <script src="/<?php echo $host_name; ?>/plugins/morris/morris.min.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="/<?php echo $host_name; ?>/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="/<?php echo $host_name; ?>/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="/<?php echo $host_name; ?>/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- jQuery Knob Chart -->
    <script src="/<?php echo $host_name; ?>/plugins/knob/jquery.knob.js" type="text/javascript"></script>
    <!-- daterangepicker -->
    <script src="/<?php echo $host_name; ?>/js/moment.min.js" type="text/javascript"></script>
    <script src="/<?php echo $host_name; ?>/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- datepicker -->
    <script src="/<?php echo $host_name; ?>/plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="/<?php echo $host_name; ?>/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <!-- Slimscroll -->
    <script src="/<?php echo $host_name; ?>/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src="/<?php echo $host_name; ?>/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="/<?php echo $host_name; ?>/dist/js/app.min.js" type="text/javascript"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <?php /*<script src="/<?php echo $host_name; ?>/dist/js/pages/dashboard.js" type="text/javascript"></script> */ ?>
    <!-- AdminLTE for demo purposes -->
    <script src="/<?php echo $host_name; ?>/dist/js/demo.js" type="text/javascript"></script>

    <!-- Validator -->
    <script src="/<?php echo $host_name; ?>/dist/js/jquery.form-validator.min.js" type="text/javascript"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" type="text/javascript"></script> -->
    <!-- <script src="/<?php echo $host_name; ?>/dist/js/jquery.form-validator.min.js" type="text/javascript"></script> -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js" type="text/javascript"></script> -->

	<script type="text/javascript">
      $(document).ready(function () {
			// Initialize ICheck Checkbox and Radio Buttons
			$('input').iCheck({
			  checkboxClass: 'icheckbox_square-blue',
			  radioClass: 'iradio_square-blue',
			  /*increaseArea: '20%' // optional*/
			  click: function(){
				  //alert('sdfsdf');
			  }
			});
			function iCheckClicked(elem){
                var for_attr = $(elem).attr('for');
            }
			//Initialize Select2 Elements
			$(".select2").select2();
            $(".select2-w100").select2({width:'100%'});
			setInterval(function(){
					$(document).find('.alert-success, .alert-danger').remove();
				},
				8000
			);
			$("[data-mask]").inputmask();
			$('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover({html: true, container: 'body'});


            $.validate({
              modules : 'date, security, location, logic'
            });

            $('body').on('focus', '.associate-autocomplete', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getAssociate.php",
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                           let asscoiateId = ui.item.id;
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                            $('.parent_id').val(ui.item.id);
                            if(asscoiateId !=''){
                                getAssociateList(asscoiateId)
                            }

                        }else{
                            $(this).val('');
                        }
                        // alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                return $( "<li class='ui-autocomplete-row'></li>" )
                .data( "item.autocomplete", item )
                .append( item.label )
                .appendTo( ul );
                };

            });

            $('body').on('focus', '.addedby-autocomplete', function() {
                $( this ).autocomplete({
                    source: "../dynamic/getAdmins.php",
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for_id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for_id')).val(ui.item.id);
                            // alert(ui.item.id);
                            // $('#addedby').val(ui.item.id);
                        }else{
                            $(this).val('');
                        }
                        //alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    }
                })
            });

            $('body').on('focus', '.associate-autocomplete-with-type', function() {
                $( this ).autocomplete({
                    source: function(request, response) {
                        $.getJSON("../dynamic/getAssociate.php", { search_for: $("#"+$(this.element).attr('data-search-for-id')).val(), term: $(this.element).val() }, response);
                    },
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                        }else{
                            $(this).val('');
                        }
                        //alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    }
                });
            });


            $('body').on('focus', '.customer-autocomplete', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getCustomer.php",
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                            $('#customer_id').val(ui.item.id)   
                        }else{
                            $(this).val('');
                        }
                        // alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                    return $( "<li class='ui-autocomplete-row'></li>" )
                    .data( "item.autocomplete", item )
                    .append( item.label )
                    .appendTo( ul );
                };
            });


            $('body').on('focus', '.farmer-autocomplete', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getFarmer.php",
                    minLength: 3,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                        }else{
                            $(this).val('');
                        }
                        //alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                    return $( "<li class='ui-autocomplete-row'></li>" )
                    .data( "item.autocomplete", item )
                    .append( item.label )
                    .appendTo( ul );
                };
            });

            $('body').on('focus', '.contact-autocomplete', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getContact.php",
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                           // let associateId = ui.item.id;
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                        }else{
                            $(this).val('');
                        }
                        //alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                    return $( "<li class='ui-autocomplete-row'></li>" )
                    .data( "item.autocomplete", item )
                    .append( item.label )
                    .appendTo( ul );
                };
            });
            $('body').on('focus', '.visit-forms-autocomplete', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getAssociateVisitForms.php",
                    minLength: 3,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                            $('.associate_id').val(ui.item.id);
                            // $('#addAssociate').children().find('input#associate_id').val(ui.item.id)
                            
                        }else{
                            $(this).val('');
                        }
                        // alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                return $( "<li class='ui-autocomplete-row'></li>" )
                .data( "item.autocomplete", item )
                .append( item.label )
                .appendTo( ul );
                };

            });
            $('body').on('focus', '.visit-forms-autocomplete-project', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getProjectDetails.php",
                    minLength: 3,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                            $('.project_id').val(ui.item.id)
                            
                        }else{
                            $(this).val('');
                        }
                        // alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                return $( "<li class='ui-autocomplete-row'></li>" )
                .data( "item.autocomplete", item)
                .append( item.label )
                .appendTo( ul );
                };

            });
            $('body').on('focus', '.account_delails_search', function() {
                $( this ).autocomplete({

                    source: "../dynamic/getAccountDetailes.php",
                    minLength: 1,
                    select: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val(ui.item.id);
                            // $('.project_id').val(ui.item.id)
                            
                        }else{
                            $(this).val('');
                        }
                        // alert( "Selected: " + ui.item.value + " aka " + ui.item.id );
                    },
                    search: function( event, ui ) {
                        if($(this).attr('data-for-id') != undefined && $(this).attr('data-for-id') != ''){
                            $("#"+$(this).attr('data-for-id')).val($(this).val());
                        }
                    }
                }).data("ui-autocomplete")._renderItem = function( ul, item ) {
                return $( "<li class='ui-autocomplete-row'></li>" )
                .data( "item.autocomplete", item)
                .append( item.label )
                .appendTo( ul );
                };

            });
	  });
      
      function getAssociateList(id){
        html='';
        $('.associate_list').html('');
        $.ajax({
            url:'../dynamic/assoicateMultipleList.php',
            type:'get',
            data:{parent_id:id},
            dataType:'json',
            success:function(resp){
                if(resp.length){
                    $.each(resp,function(index,value){
                            html += '<div class="form-group">' +
                                    '<label for="name" class="col-sm-3 control-label">Associate Name</label>' +
                                    '<div class="col-sm-5">' +
                                        '<input type="text" class="form-control associate-name" name="associate-name" value="'+value.name+'" maxlength="255" required>' +    
                                        '<input type="hidden" class="form-control associate-name" name="associate_id[]" value="'+value.id+'" maxlength="255" required>' +    
                                        '<input type="hidden" class="form-control associate-name" name="parent_id[]" value="'+value.parent_id+'" maxlength="255" required>' +    
                                    '</div>' +
                                    '<div class="col-sm-3">' +
                                        '<input type="text" class="form-control associate-name" name="associate_percentage[]" maxlength="255" required>' +    
                                    '</div>' +
                                '</div>';
                    });
                    $('.associate_list').html(html);
                }else{

                }

            }
        })
      } 
      
	</script>
