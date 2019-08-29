jQuery(document).ready(function($){

	// Табы

	function setActive(obj) {
		$(obj).siblings().removeClass('active');
		$(obj).addClass('active');
	}

	$('.ztabs .ztab').click(function (e) {
		e.preventDefault();
		setActive($(this))
		num = $(this).index();
		variants = $(this).closest('.ztabs').siblings('.ztab-content');
		setActive(variants.eq(num));
		// Для обновления шапки таблицы во вкладке
	});

	$('.ztabs').each(function(){
		setActive($( '.ztab:eq(0)', this));
	});

	$('.ztabs-block').each(function(){
		setActive($( '>.ztab-content:eq(0)', this));
	});

	function tab_content_of_tab(tab) {
		num = $(tab).index();
		variants = $(tab).closest('.ztabs').siblings('.ztab-content');
		return variants.eq(num);
	}


	// Получаем через AJAX список элементов в соответствии с аргументами функции и запускаем их обработку
	function start_ajax_list_retreiving(args) {
		if( args.get_list_function && args.process_item_function ) {
			var items_to_process = [];
			var ajax_args = {
				action: args.get_list_function,
			}
			if( args.ajax_args && args.ajax_args.length ) {
				ajax_args = $.extend(true, {}, ajax_args, args.ajax_args);
			}

			var output_block = $('.output-block#output-block');
			if( args.output_block ) {
				output_block = $(args.output_block);
			}
			args.output_block = output_block;

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: "json",
				data: ajax_args,

				beforeSend: function() {
					output_block.addClass('loading');
				},
				complete: function() {
					output_block.removeClass('loading');
				},
				success: function(data) {
					console.log(data);
					if(data.status == 'ok') {
						if(data.items && data.items.length) {
							output_block.append("<div>Получено " + data.items.length + " элементов</div>");
							args2 = args;
							args2['items_to_process'] = data.items;
							args2['ajax_args'] = ajax_args;
							args2['ajax_args']['action'] = args.process_item_function;
							ajax_process_items_list(args2);
						}
					} else {
						message = "Can't get items list with " + args.get_list_function + "()";
						output_block.append("<div>" + message + "</div>");
						console.log(message);
						console.log(args);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {  
					console.log('AJAX ERROR:');
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});
		}
	}

	// Обрабатываем через AJAX элементы из списка по одному
	function ajax_process_items_list(args) {
		var output_block = $(args.output_block);
		if(args.items_to_process.length) {
			var item_to_process = args.items_to_process.shift();

			args.ajax_args['item_to_process'] = item_to_process;

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: "json",
				data: args.ajax_args,

				beforeSend: function() {
					output_block.addClass('loading');
				},
				complete: function() {
					output_block.removeClass('loading');
				},
				success: function(data) {
					console.log(data);
					if(data.status == 'ok') {
						if( data.message && data.message.length ) {
							output_block.append("<div>" + data.message + "</div>");
						}
					} else {
						message = "Can't process item with " + args.process_item_function + "() : " + item_to_process;
						output_block.append("<div>" + message + "</div>");
						console.log(message);
						console.log(args);
					}
					ajax_process_items_list(args);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log('AJAX ERROR:');
					console.log(jqXHR);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});
		} else {
			output_block.append("<div>Обработка списка завершена</div>");
		}
	}

	$('#start-ajax-save-initial-texts').click(function(e){
		e.preventDefault();
		start_ajax_list_retreiving({
			get_list_function: 'get_all_posts_list',
			process_item_function: 'save_initial_texts',
			output_block: '#output-block-save-initial-texts',
		});
	});

	$('#start-ajax-translate-published-posts').click(function(e){
		e.preventDefault();
		start_ajax_list_retreiving({
			get_list_function: 'get_published_posts_list',
			process_item_function: 'translate_post',
			output_block: '#output-block-published-posts',
		});
	});

});