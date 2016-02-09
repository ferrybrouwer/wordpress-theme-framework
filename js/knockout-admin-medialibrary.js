(function ($, models, media, _) {
  "use strict";


  /**
   * Knockout Media Library Instance
   *
   * @param {object} obj
   * @constructor
   */
  function MediaLibraryInstance(obj) {
    try {
      _.bindAll.apply(this, [this].concat(_.methods(this)));

      this.type = obj.type || MediaLibraryInstance.types.IMAGE;
      this.previewUrl = ko.observable(obj.previewUrl || null);
      this.attachmentUrl = ko.observable(obj.attachmentUrl || null);
      this.attachmentId = ko.observable(obj.attachmentId || null);
      this.imageSizePreview = ko.observable(obj.imageSizePreview || null);
      this.imageSizeAttachment = ko.observable(obj.imageSizeAttachment || null);
      this.frameTitle = ko.observable(obj.frameTitle || null);
      this.frameButtonText = ko.observable(obj.frameButtonText || null);
      this.fileName = ko.observable(obj.fileName || null);

      this.showPreview = ko.computed(function () {
        return !_.isEmpty(this.previewUrl());
      }, this);

      this.showDeleteButton = ko.computed(function () {
        return parseInt(this.attachmentId()) > 0;
      }, this);

      this.showSelectButton = ko.computed(function () {
        return _.isNull(this.attachmentId());
      }, this);

      this.showFilename = ko.computed(function () {
        return !_.isNull(this.fileName());
      }, this);

      this.initialize();
    } catch (err) {
      console.error(err);
    }
  }

  MediaLibraryInstance.types = {
    IMAGE:       'image',
    AUDIO:       'audio',
    VIDEO:       'video',
    POST:        'post',
    MANAGE:      'manage',
    ATTACHMENTS: 'edit-attachments',
    PDF:         'application/pdf'
  };

  MediaLibraryInstance.prototype = {
    constructor: MediaLibraryInstance,

    type:                MediaLibraryInstance.types.IMAGE,
    previewUrl:          null,
    attachmentUrl:       null,
    attachmentId:        null,
    imageSizePreview:    null,
    imageSizeAttachment: null,
    frameTitle:          null,
    frameButtonText:     null,
    frame:               null,

    /**
     * Initialize media library instance
     */
    initialize: function () {
      // create wordpress media frame instance
      this.frame = media({
        frame:    'select',
        multiple: false,
        library:  {type: this.type},
        title:    this.frameTitle(),
        button:   {text: this.frameButtonText()}
      });

      // add event listener when an image is selected
      this.frame.on('select', this._fileSelected);
    },

    /**
     * Remove attachment from instance
     * nullify values
     */
    removeAttachment: function () {
      this.previewUrl(null);
      this.attachmentUrl(null);
      this.attachmentId(null);
      this.fileName(null);
    },

    /**
     * Select and add attachment from instance
     * Open frame
     */
    addAttachment: function () {
      this.frame.open();
    },

    /**
     * Event invoked when file is selected
     * @private
     */
    _fileSelected: function () {
      var attachment = this.frame.state().get('selection').first().toJSON();

      // when media type is an image, set image size
      if (this.type === MediaLibraryInstance.types.IMAGE) {

        // preview url
        if (_.has(attachment.sizes, this.imageSizePreview())) {
          var previewSizeObject = attachment.sizes[this.imageSizePreview()];
          this.previewUrl(previewSizeObject.url);
        } else {
          this.previewUrl(attachment.url);
        }

        // attachment url
        if (_.has(attachment.sizes, this.imageSizeAttachment())) {
          var attachmentSizeObject = attachment.sizes[this.imageSizeAttachment()];
          this.attachmentUrl(attachmentSizeObject.url);
        } else {
          this.attachmentUrl(attachment.url);
        }
      } else {
        this.attachmentUrl(attachment.url);
      }

      // filename
      this.fileName(attachment.filename);

      // attachment id
      this.attachmentId(attachment.id);
    }
  };


  /**
   * Knockout Media Library
   * @constructor
   */
  function MediaLibary() {
    _.bindAll.apply(this, [this].concat(_.methods(this)));
    this.instances = ko.observableArray();
  }

  MediaLibary.prototype = {
    constructor: MediaLibary,

    /**
     * Create new instance
     * @returns {MediaLibraryInstance}
     */
    newInstance: function (obj) {
      var instance = new MediaLibraryInstance(obj);
      this.instances.push(instance);
      return instance;
    }
  };

  models.medialibrary = new MediaLibary();

})(jQuery, window.happy.models, window.wp.media, window._);
