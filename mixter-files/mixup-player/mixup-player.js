var MIXUP_PLAYER_ID = 'mixup-player';
var MIXUP_PLAYER_UI_ID = 'mixup-player-ui';
var MIXUP_PLAYER_CONTROLS_ID = 'mixup-player-controls';
var MIXUP_PLAYER_BUTTON_CONTAINER_ID = 'mixup-player-button-container';
var MIXUP_PLAYER_TIME_DISPLAY_ID = 'mixup-player-time-display';
var MIXUP_PLAYER_SCRUBBER_ID = 'mixup-player-scrubber';
var MIXUP_PLAYER_LOADED_ID = 'mixup-player-loaded';
var MIXUP_PLAYER_POSITION_ID = 'mixup-player-position';
var MIXUP_PLAYER_SCRUBBER_CLICKER_ID = 'mixup-player-scrubber-clicker';
var MIXUP_PLAYER_PLAY_PAUSE_BUTTON_ID = 'mixup-player-playpause';
var MIXUP_PLAYER_PREVIOUS_BUTTON_ID = 'mixup-player-previous';
var MIXUP_PLAYER_NEXT_BUTTON_ID = 'mixup-player-next';
var MIXUP_PLAYER_VOLUME_CONTAINER_ID = 'mixup-player-volume-container';
var MIXUP_PLAYER_VOLUME_CONTROL_CONTAINER_ID = 'mixup-player-volume-control-container';
var MIXUP_PLAYER_VOLUME_CONTROL_ID = 'mixup-player-volume-control';
var MIXUP_PLAYER_VOLUME_TOGGLE_ID = 'mixup-player-volume-toggle';
var MIXUP_BUTTON_CLASS = 'mixup-button';
var MIXUP_PLAYLIST_ID = 'mixup-playlist';
var MIXUP_TRACK_ROW_CLASS = 'mixup-track-row';
var MIXUP_TRACK_BUTTON_CLASS = 'mixup-track-button';
var MIXUP_TRACK_PLAY_PAUSE_BUTTON_CLASS = 'mixup-track-playpause';
var MIXUP_INFO_BUTTON_CLASS = 'mixup-info';
var MIXUP_ACTION_BUTTON_CLASS = 'menuup_hook';
var MIXUP_CURRENT_TRACK_CLASS = 'mixup-current-track';

var mPlayer = null;

var MixupPlayer = Class.create();

MixupPlayer.prototype = {
  playlist: [],
  volume: 0.5,
  playlist_cursor: 0,
  playing: false,
  paused: false,
  audio: null,

  initialize: function (playlist, options) {
    this.playlist = playlist;
    this.audio = new Audio();
    this.audio.volume = this.volume;
    this.audio.controls = false;

    mPlayer = this;
  },

  render: function () {
    var output = '<div id="' + MIXUP_PLAYER_ID + '">';
    output += this._renderPlayer();
    output += this._renderPlaylist();
    output += '</div>';

    return output;
  },

  hookEvents: function () {
    this._hookPlayerPlayButton();
    this._hookTrackPlayButtons();
    this._hookPreviousButton();
    this._hookNextButton();
    this._hookArrowKeys();
    this._hookSpaceBar();
    this._hookScrubber();
    this._hookVolumeControls();
  },

  _needsStart: function () {
    return (!this.playing && !this.paused);
  },

  _hookPlayerPlayButton: function () {
    var self = this;
    var button = document.getElementById(MIXUP_PLAYER_PLAY_PAUSE_BUTTON_ID);
    button.addEventListener("click", function (e) {
      e.preventDefault();
      self._onPlayButtonClick(self.playlist_cursor);
    });
  },

  _hookTrackPlayButtons: function () {
    var self = this;
    var playButtons = document.getElementsByClassName(MIXUP_TRACK_PLAY_PAUSE_BUTTON_CLASS);
    playButtons.forEach(function (button) {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        var currentTrackIndex = this.parentNode.parentNode.attributes['data-index'].value;

        self._onPlayButtonClick(currentTrackIndex);
      });
    });
  },

  _hookPreviousButton: function () {
    var self = this;
    var button = document.getElementById(MIXUP_PLAYER_PREVIOUS_BUTTON_ID);
    button.addEventListener("click", function (e) {
      e.preventDefault();

      self._previous();
    });
  },

  _hookArrowKeys: function () {
    var self = this;

    window.addEventListener("keydown", function (e) {
      if (e.keyCode === 37) {
        self._previous();
      }

      if (e.keyCode === 39) {
        self._next();
      }

      if (e.keyCode === 38) {
        e.preventDefault();

        if (self.audio.volume < 1) {
          var newVolume = (Number.parseFloat(self.audio.volume) + Number.parseFloat(0.1)).toFixed(1);
          self.audio.volume = self.volume = (newVolume > 1) ? 1 : newVolume;
          self._setVolumeDisplay(self.audio.volume);
        }
      }

      if (e.keyCode === 40) {
        e.preventDefault();

        if (self.audio.volume > 0) {
          var newVolume = (Number.parseFloat(self.audio.volume) - Number.parseFloat(0.1)).toFixed(1);
          self.audio.volume = self.volume = (newVolume < 0) ? 0 : newVolume;
          self._setVolumeDisplay(self.audio.volume);
        }
      }
    });
  },

  _hookSpaceBar: function () {
    var self = this;

    window.addEventListener("keydown", function (e) {
      if (e.keyCode === 32) {
        e.preventDefault();

        self._onPlayButtonClick(self.playlist_cursor);
      }
    });
  },

  _hookNextButton: function () {
    var self = this;
    var button = document.getElementById(MIXUP_PLAYER_NEXT_BUTTON_ID);
    button.addEventListener("click", function (e) {
      e.preventDefault();

      self._next();
    });
  },

  _hookScrubber: function () {
    var self = this;
    var scrubber = document.getElementById(MIXUP_PLAYER_SCRUBBER_CLICKER_ID);

    scrubber.addEventListener("click", function (e) {
      var rect = e.target.getBoundingClientRect();
      var width = Math.round(rect.width);
      var clickX = Math.round(e.pageX - rect.x);
      var percent = clickX / width;
      var bufferedDuration = self.audio.buffered.end(0);
      var newPosition = bufferedDuration * percent;

      self.audio.currentTime = newPosition;
    });
  },

  _hookVolumeControls: function () {
    this._hookVolumeControl();
    this._hookVolumeToggle();
  },

  _hookVolumeControl: function () {
    var self = this;
    var volumeControlContainer = document.getElementById(MIXUP_PLAYER_VOLUME_CONTROL_CONTAINER_ID);

    self._setVolumeDisplay(this.audio.volume);

    volumeControlContainer.addEventListener('click', function (e) {
      var rect = e.currentTarget.getBoundingClientRect();
      var width = Math.round(rect.width);
      var clickX = Math.round(e.pageX - rect.x);
      var newVolume = clickX / width;

      self.audio.volume = self.volume = newVolume;
      self._setVolumeDisplay(newVolume);
    }, true);
  },

  _hookVolumeToggle: function () {
    var self = this;
    var toggle = document.getElementById(MIXUP_PLAYER_VOLUME_TOGGLE_ID);

    toggle.addEventListener('click', function (e) {
      e.preventDefault();

      if (self.audio.volume === 0) {
        self.audio.volume = self.volume;
        e.currentTarget.firstChild.className = 'fas fa-volume-up';
      } else {
        self.audio.volume = 0;
        e.currentTarget.firstChild.className = 'fas fa-volume-off';
      }

      self._setVolumeDisplay(self.audio.volume);
    });
  },

  _onPlayButtonClick: function (index) {
    if (this._needsStart() || (this.playlist_cursor !== index)) {
      this._start(index);
    } else {
      if (this.paused) {
        this._unpause();
      } else {
        this._pause();
      }
    }
  },

  _start: function (index) {
    var self = this;
    var loaded = document.getElementById(MIXUP_PLAYER_LOADED_ID);
    var scrubber = document.getElementById(MIXUP_PLAYER_SCRUBBER_CLICKER_ID);
    var position = document.getElementById(MIXUP_PLAYER_POSITION_ID);

    this.playlist_cursor = index;
    this.audio.src = this._trackAudio();
    this.audio.preload = 'auto';
    this.paused = false;
    this.playing = true;
    this._highlightCurrent();
    this._togglePlayButtonIcon();

    this.audio.play();

    this.audio.addEventListener("timeupdate", function (e) {
      if (e.target.buffered.length > 0) {
        var percentLoaded = Math.round((e.target.buffered.end(0) / e.target.duration) * 100);
        var currentPosition = Math.round((e.target.currentTime / e.target.duration) * 100);
        var timeDisplay = document.getElementById(MIXUP_PLAYER_TIME_DISPLAY_ID);

        loaded.setAttribute('style', 'width: ' + percentLoaded + '%');
        scrubber.setAttribute('style', 'width: ' + percentLoaded + '%');
        position.setAttribute('style', 'width: ' + currentPosition + '%');

        timeDisplay.innerText = self._calculateCurrentValue(self.audio.currentTime) + ' / ' + self._calculateTotalValue(self.audio.duration);
      }
    });

    function endedHandler(e) {
      self.audio.removeEventListener('ended', endedHandler);
      self._next();
    }

    this.audio.addEventListener("ended", endedHandler);

  },

  _previous: function () {
    var index;

    if (parseInt(this.playlist_cursor) === 0) {
      index = this.playlist.length - 1;
    } else {
      index = parseInt(this.playlist_cursor) - 1;
    }
    this._start(index);
  },

  _next: function () {
    var index;

    if (parseInt(this.playlist_cursor) === this.playlist.length - 1) {
      index = 0;
    } else {
      index = parseInt(this.playlist_cursor) + 1;
    }

    this._start(index);
  },

  _pause: function () {
    this.paused = true;
    this._togglePlayButtonIcon();
    this.audio.pause();
  },

  _unpause: function () {
    this.paused = false;
    this._togglePlayButtonIcon();
    this.audio.play();
  },

  _setVolumeDisplay: function (volume) {
    var volumeControl = document.getElementById(MIXUP_PLAYER_VOLUME_CONTROL_ID);
    volumeControl.setAttribute('style', 'width: ' + Math.round(volume * 100) + '%');
  },

  _highlightCurrent: function () {
    // before we highlight current tack's table row
    // let’s make sure all rows are cleared
    var rows = document.getElementsByClassName(MIXUP_TRACK_ROW_CLASS);
    rows.forEach(function (item) {
      item.className = MIXUP_TRACK_ROW_CLASS;
    });

    var id = this._trackId(
      this.playlist[this.playlist_cursor],
      this.playlist_cursor
    );

    // The <tr>, which is this play button can be found within
    var current = document.getElementById(id).parentElement.parentElement;

    current.className = MIXUP_TRACK_ROW_CLASS + ' ' + MIXUP_CURRENT_TRACK_CLASS;
  },

  _togglePlayButtonIcon: function () {
    var className;

    if (this._needsStart() || this.paused) {
      className = 'fas fa-play-circle';
    } else {
      className = 'fas fa-pause-circle';
    }

    // reset all track play buttons
    var trackButtons = document.getElementsByClassName(MIXUP_TRACK_PLAY_PAUSE_BUTTON_CLASS);

    trackButtons.forEach(function (item) {
      item.firstChild.className = 'fas fa-play-circle';
    });

    // set player play button icon
    var playerPlayButton = document.getElementById(MIXUP_PLAYER_PLAY_PAUSE_BUTTON_ID);
    playerPlayButton.firstChild.className = className;

    // set track play button icon
    var currentTrackPlayButton = document.getElementById(this._trackId(
      this.playlist[this.playlist_cursor],
      this.playlist_cursor
    ));
    currentTrackPlayButton.firstChild.className = className;
  },

  _renderPlayer: function () {
    var controls = this._renderPlayerControls();
    var scrubber = this._renderScrubber();

    return '<div id="' + MIXUP_PLAYER_UI_ID + '">' + controls + scrubber + '</div>';
  },

  _renderPlayerControls: function () {
    var volumeControl = this._renderVolumeControl();
    var playerButtons = this._renderPlayerButtons();
    var playerTimeDisplay = this._renderPlayerTimeDisplay();

    return '<div id="' + MIXUP_PLAYER_CONTROLS_ID + '">' + volumeControl + playerButtons + playerTimeDisplay +  '</div>';
  },

  _renderVolumeControl: function () {
    var control = '<span id="' + MIXUP_PLAYER_VOLUME_CONTROL_ID + '" aria-label="Volume"></span>';
    var controlContainer = '<div id="' + MIXUP_PLAYER_VOLUME_CONTROL_CONTAINER_ID + '">' + control + '</div>';
    var toggle = '<a href="#" id="' + MIXUP_PLAYER_VOLUME_TOGGLE_ID + '" class="mixup-button"><i class="fas fa-volume-up "></i></a>';
    var flex = '<div class="flex">' + controlContainer + ' ' + toggle + '</div>';

    return '<div id="' + MIXUP_PLAYER_VOLUME_CONTAINER_ID + '">' + flex + '</div>';
  },

  _renderPlayerButtons: function () {
    var prevButton = '<a href="" id="' + MIXUP_PLAYER_PREVIOUS_BUTTON_ID + '" class="' + MIXUP_BUTTON_CLASS + '" aria-label="Previous"><i class="fas fa-backward"></i></a>';
    var nextButton = '<a href="" id="' + MIXUP_PLAYER_NEXT_BUTTON_ID + '" class="' + MIXUP_BUTTON_CLASS + '" aria-label="Next"><i class="fas fa-forward"></i></a>';
    var playButton = '<a id="' + MIXUP_PLAYER_PLAY_PAUSE_BUTTON_ID + '" class="' + MIXUP_BUTTON_CLASS + '" aria-label="Play All"><i class="fas fa-play-circle"></i></a>';

    return '<div id="' + MIXUP_PLAYER_BUTTON_CONTAINER_ID + '">' + prevButton + ' ' + playButton + ' ' + nextButton + '</div>';
  },

  _renderPlayerTimeDisplay: function () {
    return '<div id="' + MIXUP_PLAYER_TIME_DISPLAY_ID + '"></div>';
  },

  _renderScrubber: function () {
    return '<div id="' + MIXUP_PLAYER_SCRUBBER_ID +'"><div id="' + MIXUP_PLAYER_LOADED_ID + '"></div><div id="' + MIXUP_PLAYER_POSITION_ID + '"></div><div id="' + MIXUP_PLAYER_SCRUBBER_CLICKER_ID + '"></div></div>';
  },

  _renderPlaylist: function () {
    var output = '<table id="' + MIXUP_PLAYLIST_ID + '">';

    var self = this;
    this.playlist.forEach(function (item, index) {
      output += self._renderTrack(item, index);
    });

    output += '</table>';

    return output;
  },

  _renderTrack: function (track, index) {
    var output = '<tr class="' + MIXUP_TRACK_ROW_CLASS + '" data-index="' + index + '">';
    output += '<td>' + this._renderTrackPlayButton(track, index) + '</td>';
    output += '<td>' + this._renderTrackName(track) + '</td>';
    output += '<td class="nowrap center">' + this._renderActionButtons(track) + '</td>';
    output += '</tr>';

    return output;
  },

  _renderTrackPlayButton: function (track, index) {
    return '<a id="' + this._trackId(track, index) + '" class="' + MIXUP_TRACK_BUTTON_CLASS + ' ' + MIXUP_TRACK_PLAY_PAUSE_BUTTON_CLASS + '" aria-label="Play Track"><i class="fas fa-play-circle"></i></a>';
  },

  _renderTrackName: function (track) {
    var output = this._link(track.artist_page_url, track.user_real_name) + ' remix of ';
    output += this._link(track.mixee_page_url, track.mixee_name) + ' ';
    output += this._link(track.file_page_url, '“' + track.upload_name + '”');

    return output;
  },

  _renderActionButtons: function (track) {
    var output = this._link(null, '<i class="fas fa-info-circle"></i>', '_plinfo_' + track.upload_id, MIXUP_TRACK_BUTTON_CLASS + ' ' + MIXUP_INFO_BUTTON_CLASS) + ' ';
    output += this._link(null, '<i class="fas fa-wrench"></i>', '_plaction_' + track.upload_id, MIXUP_TRACK_BUTTON_CLASS + ' ' + MIXUP_ACTION_BUTTON_CLASS);

    return output;
  },

  _trackId: function (track, index) {
    return '_plplay_' + track.upload_id + '_' + index;
  },

  _trackAudio: function () {
    console.log(this.playlist[this.playlist_cursor].fplay_url);

    return this.playlist[this.playlist_cursor].fplay_url;
  },

  _calculateTotalValue: function (length) {
    var minutes = Math.floor(length / 60);
    var seconds = Math.round(length - minutes * 60);
    var time = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);

    return time;
  },

  _calculateCurrentValue: function (currentTime) {
    var hour = parseInt(currentTime / 3600) % 24;
    var minute = parseInt(currentTime / 60) % 60;
    var seconds = (currentTime % 60).toFixed();
    var time = minute + ":" + (seconds < 10 ? '0' + seconds : seconds);

    if (hour > 0) {
      time = hour + ':' + time;
    }

    return time;
  },

  _link: function (url, label, id, cl) {
    if (id === undefined) {
      id = '';
    }

    if (cl === undefined) {
      cl = '';
    }

    if (url === null) {
      return '<a id="' + id + '" class="' + cl + '">' + label + '</a>';
    }

    return '<a href="' + url + '" id="' + id + '" class="' + cl + '">' + label + '</a>';
  }
};