/**
 * Application-init file for Ocdla skin
 */
// alert('new skin JS loaded.');
$(function(){
	$('#powersearch').attr('data-method','checkForBooksOnline');
	$('#powersearch').attr('data-event','change');

	 $('#mw-searchoptions').prepend('<input id="ocdla-search-books-online-toggle" data-method="toggleAllBooks" value="All Books Online" title="Toggle Books Online products" type="button" />');

	var searchHelper = extend(null,{	
		salesOrderId: null,
		
		// By default we're already searching all books online products.
		allChecked: true,
	
		init: function(){
			this.hideAllBooksTalkNamespaces();

		},
	
		load: function(salesOrderId) {
	
		},
		
		hideAllBooksTalkNamespaces: function(){
			this.booksOnlineTalkNamespaces.map(this.booksOnlineSearchFormInputBoxes).forEach(function(id){
				$('#'+id).css("display","none");
				$('label[for='+id+']').css("display","none");
			});
		},
		
		toggleAllBooks: function(e,onOff){
			var localCheckAll = onOff||!this.allChecked;
			this.booksOnlineNamespaces.map(this.booksOnlineSearchFormInputBoxes).forEach(function(id){
				console.log(id +': '+localCheckAll);
				$('#'+id).prop("checked",localCheckAll);
			});
			this.allChecked = localCheckAll;
		},
		
		isBooksOnlineNamespace: function(ns){},
		
		checkForBooksOnline: function(e) {
			console.log(e.target.nodeName);
			if(e.target.nodeName == 'INPUT' && e.target.getAttribute('type') == 'checkbox'){
				e.stopPropagation();
				// console.log(e.target.checked);
				//if(e.targe.getAttribute('checked')this.toggleAllBooks(false);
			}

			return false;
		},
		
		
		/*
		define("NS_DTN",				 				500);
define("NS_DTN_TALK", 					501);

define("NS_FSM",				 				504);
define("NS_FSM_TALK", 					505);

define("NS_IM",					 				508);
define("NS_IM_TALK", 						509);

// Left off here
define("NS_MH",					 				510);
define("NS_MH_TALK", 						511);

define("NS_PJ",					 				512);
define("NS_PJ_TALK", 						513);

define("NS_SE",					 				514);
define("NS_SE_TALK", 						515);

define("NS_SSM",					 			516);
define("NS_SSM_TALK", 					517);

define("NS_TNB",					 			518);
define("NS_TNB_TALK", 					519);
*/
		
		booksOnlineNamespaces: [500,504,508,510,512,514,516,518],
		
		booksOnlineTalkNamespaces: [501,505,509,511,513,515,517,519],
		
		booksOnlineSearchFormInputBoxes: function(item){
			return "mw-search-ns"+item;
		},
	
	});
	searchHelper._init();
	// searchHelper.load(j$.getUrlVar('id'));
});