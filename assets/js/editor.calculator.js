(function(){
  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var RangeControl = wp.components.RangeControl;
  var ToggleControl = wp.components.ToggleControl;
  var MediaUpload = (wp.blockEditor && wp.blockEditor.MediaUpload) || (wp.editor && wp.editor.MediaUpload);
  var Button = wp.components.Button;
  var ColorPalette = (wp.blockEditor && wp.blockEditor.ColorPalette) || (wp.editor && wp.editor.ColorPalette);

  function ImagePicker(props){
    return el(MediaUpload, {
      onSelect: function(media){ props.onChange({ id: media.id, url: media.url }); },
      allowedTypes: ['image'],
      render: function(obj){
        return el('div',{className:'irank-ctrl-img'},[
          props.valueUrl ? el('img',{src:props.valueUrl, style:{maxWidth:'100%',height:'auto'}}) : null,
          el(Button,{onClick: obj.open, isSecondary:true}, props.label || __('Select Image','irank-calc-cards'))
        ]);
      }
    });
  }

  registerBlockType('irank/weight-loss-calculator',{
    title: __('Weight Loss Calculator','irank-calc-cards'),
    icon: 'calculator',
    category: 'widgets',
    attributes: {
      minWeight:{type:'number',default:100},
      maxWeight:{type:'number',default:400},
      step:{type:'number',default:1},
      initialWeight:{type:'number',default:200},
      lossFactor:{type:'number',default:0.15},
      unit:{type:'string',default:'lbs'},
      beforeImageId:{type:'number',default:0},
      beforeImage:{type:'string',default:''},
      afterImageId:{type:'number',default:0},
      afterImage:{type:'string',default:''},
      ctaText:{type:'string',default:__('Get started','irank-calc-cards')},
      showTimer:{type:'boolean',default:true},
      timerText:{type:'string',default:__('Get pre-approved in under 90 seconds!','irank-calc-cards')},
      gradientStart:{type:'string',default:'#FFBB8E'},
      gradientEnd:{type:'string',default:'#F0532C'},
    },
    edit: function(props){
      var a = props.attributes;
      return [
        el(InspectorControls,{},
          el(PanelBody,{title:__('Calculator Settings','irank-calc-cards'),initialOpen:true},[
            el(RangeControl,{label:__('Min Weight','irank-calc-cards'),min:50,max:400,value:a.minWeight,onChange:function(v){props.setAttributes({minWeight:v});}}),
            el(RangeControl,{label:__('Max Weight','irank-calc-cards'),min:a.minWeight||50,max:600,value:a.maxWeight,onChange:function(v){props.setAttributes({maxWeight:v});}}),
            el(RangeControl,{label:__('Initial Weight','irank-calc-cards'),min:a.minWeight,max:a.maxWeight,value:a.initialWeight,onChange:function(v){props.setAttributes({initialWeight:v});}}),
            el(RangeControl,{label:__('Step','irank-calc-cards'),min:1,max:10,value:a.step,onChange:function(v){props.setAttributes({step:v});}}),
            el(TextControl,{label:__('Loss Factor','irank-calc-cards'),value:a.lossFactor,onChange:function(v){props.setAttributes({lossFactor:parseFloat(v)||0});}}),
            el(TextControl,{label:__('Unit','irank-calc-cards'),value:a.unit,onChange:function(v){props.setAttributes({unit:v});}}),
            el(ImagePicker,{label:__('Before Image','irank-calc-cards'),valueUrl:a.beforeImage,onChange:function(obj){props.setAttributes({beforeImage:obj.url, beforeImageId:obj.id});}}),
            el(ImagePicker,{label:__('After Image','irank-calc-cards'),valueUrl:a.afterImage,onChange:function(obj){props.setAttributes({afterImage:obj.url, afterImageId:obj.id});}}),
            el(TextControl,{label:__('CTA Text','irank-calc-cards'),value:a.ctaText,onChange:function(v){props.setAttributes({ctaText:v});}}),
            el(ToggleControl,{label:__('Show Timer','irank-calc-cards'),checked:a.showTimer,onChange:function(v){props.setAttributes({showTimer:v});}}),
            el(TextControl,{label:__('Timer Text','irank-calc-cards'),value:a.timerText,onChange:function(v){props.setAttributes({timerText:v});}}),
            el('div',{},[
              el('label',{},__('Gradient Start','irank-calc-cards')),
              el(ColorPalette,{value:a.gradientStart,onChange:function(v){props.setAttributes({gradientStart:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Gradient End','irank-calc-cards')),
              el(ColorPalette,{value:a.gradientEnd,onChange:function(v){props.setAttributes({gradientEnd:v});}})
            ])
          ])
        ),
        el('div',{className:'irank-calc-preview',style:{border:'1px dashed #ddd',padding:'16px'}},[
          el('strong',{},__('Weight Loss Calculator Preview','irank-calc-cards')),
          el('div',{}, __('Initial Weight: ','irank-calc-cards') + a.initialWeight + ' ' + a.unit),
          el('div',{}, __('Loss Factor: ','irank-calc-cards') + a.lossFactor)
        ])
      ];
    },
    save: function(){ return null; }
  });
})();
