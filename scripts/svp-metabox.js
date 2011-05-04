var SVP_Metabox = new Object() || SVP_Metabox;
jQuery(document).ready(
	function ($) {

		$('a.svp-play').click(function (e) {
			var $target = $(e.target);
			SVP_Metabox._params.target = $target;
			SVP_Metabox.Show();
		});

		$('a.svp-next').live('click', function (e) {
			SVP_Metabox.Next(e);
		});

		$('a.svp-previous').live('click', function (e) {
			SVP_Metabox.Previous(e);
		});

		$('a.svp-select').live('click', function (e) {
			var $target = $(e.target);
			if ($("#svp-player-container").is(':hidden') == true)
				SVP_Metabox._params.target = $target;
			SVP_Metabox.Select(e);
		});

		SVP_Metabox._collect = function () {
			var $target = SVP_Metabox._params.target;
			SVP_Metabox._params.video_url = $target.parent('span.svp-action').children('span.svp-video-url').html();
			SVP_Metabox._params.delivery_method = 'ProgressiveDownload';
			if ($target.parent('span.svp-action').children('span.svp-delivery-method').html() != '')
				SVP_Metabox._params.delivery_method = $target.parent('span.svp-action').children('span.svp-delivery-method').html();
		},

		SVP_Metabox._inject = function () {
			this._collect();
			var $host = $('<div id="svp-silverlight-host"></div>');
			$host.html(
				Silverlight.createObject(
					this._params.player_url,
					null,
					'svp-sl-player',
					{
						width: '100%',
						height: '100%',
						background: 'transparent',
						minRuntimeVersion: '4.0.50401.0',
						autoUpgrade: 'true',
						windowless: 'true'
					},
					null,
					'MediaUrl=' + this._params.video_url + ',DeliveryMethod=' + this._params.delivery_method
				)
			);
			$host.appendTo($("#svp-player-container"));
			var links = '<a href="javascript:void(0);" class="svp-button svp-close close" title="' + this._params.close_label + '"><span>' + this._params.close_label + '</span></a>';
			links += this._get_previous_button();
			links += this._get_next_button();
			links += this._get_select_button();
			$(links).appendTo($("#svp-player-container"));
			var video_data = this._get_video_data();
			$(video_data).appendTo($("#svp-player-container"));
			$("#svp-player-container").trigger('onInject');
		},

		SVP_Metabox._update = function () {
			this._collect();
			$host = $('#svp-silverlight-host');
			$host.html('');
			$host.html(
				Silverlight.createObject(
					this._params.player_url,
					null,
					'svp-sl-player',
					{
						width: '100%',
						height: '100%',
						background: 'transparent',
						minRuntimeVersion: '4.0.50401.0',
						autoUpgrade: 'true',
						windowless: 'true'
					},
					null,
					'MediaUrl=' + this._params.video_url + ',DeliveryMethod=' + this._params.delivery_method
				)
			);
			$('#svp-player-container').children('a.svp-previous').removeClass('svp-disabled');
			if (this._has_previous_video() == false)
				$('#svp-player-container').children('a.svp-previous').addClass('svp-disabled');
			$('#svp-player-container').children('a.svp-next').removeClass('svp-disabled');
			if (this._has_next_video() == false)
				$('#svp-player-container').children('a.svp-next').addClass('svp-disabled');
			$('#svp-player-container').children('a.svp-select').removeClass('svp-activated');
			if (this._is_selected_video() == true)
				$('#svp-player-container').children('a.svp-select').addClass('svp-activated');
			$('#svp-video-data').children('span.svp-video-filename').html(this._get_video_filename());
			$('#svp-video-data').children('span.svp-source-name').html(this._get_source_name());
		},

		SVP_Metabox._destroy = function () {
			$("#svp-player-container").html('');
		},

		SVP_Metabox._get_previous_button = function () {
			var disabled = '';
			var $target = SVP_Metabox._params.target;
			if ($target.parent('span.svp-action').parent('li.svp-row-content').prev('li.svp-row-content').size() == 0)
				disabled = ' svp-disabled';
			return '<a href="javascript:void(0);" class="svp-button svp-previous' + disabled + '" title="' + this._params.previous_label + '"><span>' + this._params.previous_label + '</span></a>';
		},

		SVP_Metabox._get_next_button = function () {
			var disabled = '';
			var $target = SVP_Metabox._params.target;
			if ($target.parent('span.svp-action').parent('li.svp-row-content').next('li.svp-row-content').size() == 0)
				disabled = ' svp-disabled';
			return '<a href="javascript:void(0);" class="svp-button svp-next' + disabled + '" title="' + this._params.next_label + '"><span>' + this._params.next_label + '</span></a>';
		},

		SVP_Metabox._get_select_button = function () {
			var activated = '';
			if (this._get_current_video_id() == $('#svp-video').val())
				activated = ' svp-activated';
			return '<a href="javascript:void(0);" class="svp-button svp-select' + activated + '" title="' + this._params.select_label + '"><span>' + this._params.select_label + '</span></a>';
		},

		SVP_Metabox._has_previous_video = function () {
			var $target = SVP_Metabox._params.target;
			if ($target.parent('span.svp-action').parent('li.svp-row-content').prev('li.svp-row-content').size() == 0)
				return false;
			return true;
		},

		SVP_Metabox._has_next_video = function () {
			var $target = SVP_Metabox._params.target;
			if ($target.parent('span.svp-action').parent('li.svp-row-content').next('li.svp-row-content').size() == 0)
				return false;
			return true;
		},

		SVP_Metabox._get_current_video_id = function () {
			var $target = SVP_Metabox._params.target;
			var video_url_id = $target.parent('span.svp-action').children('span.svp-video-url').attr('id');
			var prefix = 'svp-video-url-';
			return video_url_id.substr(parseInt(prefix.length), parseInt(video_url_id.length) - parseInt(prefix.length));
		},

		SVP_Metabox._is_selected_video = function () {
			var $target = SVP_Metabox._params.target;
			if (this._get_current_video_id() != $('#svp-video').val())
				return false;
			return true;
		},

		SVP_Metabox._get_video_filename = function () {
			var $target = SVP_Metabox._params.target;
			return $target.parent('span.svp-action').prev().prev('span.svp-video-name').html();
		},

		SVP_Metabox._get_source_name = function () {
			var $target = SVP_Metabox._params.target;
			return $target.parent('span.svp-action').prev('span.svp-source-name').html();
		},

		SVP_Metabox._get_video_data = function () {
			var html = '<p id="svp-video-data" class="svp-text-center svp-little-line-height">';
			html += '<span class="svp-label svp-highlight svp-video-filename">' + this._get_video_filename() + '</span>';
			html += '<br />';
			html += '<span class="svp-label svp-downlight svp-source-name">' + this._get_source_name() + '</span>';
			html += '</p>';
			return html;
		},

		SVP_Metabox.Show = function () {
			var me = this;
			$('#svp-player-container').bind('onInject', function (e) {
				$('#svp-player-container').lightbox_me({
					centered: true,
					onClose: function () { me._destroy(); }
				});
			});
			this._inject();
		},

		SVP_Metabox.Next = function (e) {
			var $from = $(e.target);
			if ($from.hasClass('svp-disabled') == false) {
				var $target = SVP_Metabox._params.target;
				SVP_Metabox._params.target = $target.parent('span.svp-action').parent('li.svp-row-content').next('li.svp-row-content').children('span.svp-action').children('a.svp-play');
				if (SVP_Metabox._params.target != null)
					this._update();
			}
		},

		SVP_Metabox.Previous = function (e) {
			var $from = $(e.target);
			if ($from.hasClass('svp-disabled') == false) {
				var $target = SVP_Metabox._params.target;
				SVP_Metabox._params.target = $target.parent('span.svp-action').parent('li.svp-row-content').prev('li.svp-row-content').children('span.svp-action').children('a.svp-play');
				if (SVP_Metabox._params.target != null)
					this._update();
			}
		}

		SVP_Metabox.Select = function (e) {
			var $from = $(e.target);
			if ($from.hasClass('svp-activated') == false) {
				$('a.svp-select').removeClass('svp-activated');
				var $target = SVP_Metabox._params.target;
				$target.parent('span.svp-action').children('a.svp-select').addClass('svp-activated');
				var current_video_id = this._get_current_video_id();
				$('#svp-video').val(current_video_id);
				$from.addClass('svp-activated');
			}
			else {
				$('a.svp-select').removeClass('svp-activated');
				$('#svp-video').val(0);
			}
		}
	}
);