(function(){
  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var RangeControl = wp.components.RangeControl;
  var ToggleControl = wp.components.ToggleControl;
  var SelectControl = wp.components.SelectControl;
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
      gradientEnd:{type:'string',default:'#f67a51'},
      questionText:{type:'string',default:__('How much weight can you lose','irank-calc-cards')},
      weightLabel:{type:'string',default:__('My current weight:','irank-calc-cards')},
      lossLabel:{type:'string',default:__('Weight loss potential:','irank-calc-cards')},
      beforeLabel:{type:'string',default:__('Before','irank-calc-cards')},
      afterLabel:{type:'string',default:__('After','irank-calc-cards')},
      // Typography
      questionFontFamily:{type:'string',default:'Nohemi'},
      questionFontWeight:{type:'number',default:600},
      questionFontSize:{type:'string',default:'48px'},
      questionColor:{type:'string',default:'#ffffff'},

      weightFontFamily:{type:'string',default:'Poppins'},
      weightFontWeight:{type:'number',default:600},
      weightFontSize:{type:'string',default:'14px'},
      weightColor:{type:'string',default:'#ffffff'},

      lossFontFamily:{type:'string',default:'Poppins'},
      lossFontWeight:{type:'number',default:600},
      lossFontSize:{type:'string',default:'14px'},
      lossColor:{type:'string',default:'#ffffff'},

      beforeFontFamily:{type:'string',default:'Poppins'},
      beforeFontWeight:{type:'number',default:600},
      beforeFontSize:{type:'string',default:'12px'},
      beforeColor:{type:'string',default:'#ffffff'},

      afterFontFamily:{type:'string',default:'Poppins'},
      afterFontWeight:{type:'number',default:600},
      afterFontSize:{type:'string',default:'12px'},
      afterColor:{type:'string',default:'#ffffff'},

      ctaFontFamily:{type:'string',default:'Poppins'},
      ctaFontWeight:{type:'number',default:600},
      ctaFontSize:{type:'string',default:'18px'},
      ctaColor:{type:'string',default:'#ffffff'},

      timerFontFamily:{type:'string',default:'Poppins'},
      timerFontWeight:{type:'number',default:500},
      timerFontSize:{type:'string',default:'14px'},
      timerColor:{type:'string',default:'#ffffff'},

      // Button colors (CTA and Before/After labels)
      ctaBg:{type:'string',default:'#92245A'},
      ctaHoverBg:{type:'string',default:'#ffffff'},
      ctaHoverColor:{type:'string',default:'#000000'},
      ctaHoverBorder:{type:'string',default:'#000000'},

      labelBg:{type:'string',default:'#92245A'},
      labelColor:{type:'string',default:'#ffffff'},
      labelHoverBg:{type:'string',default:'#ffffff'},
      labelHoverColor:{type:'string',default:'#000000'},
      labelHoverBorder:{type:'string',default:'#000000'},
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
            el(ToggleControl,{label:__('Show Timer','irank-calc-cards'),checked:a.showTimer,onChange:function(v){props.setAttributes({showTimer:v});}}),
            el('div',{},[
              el('label',{},__('Gradient Start','irank-calc-cards')),
              el(ColorPalette,{value:a.gradientStart,onChange:function(v){props.setAttributes({gradientStart:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Gradient End','irank-calc-cards')),
              el(ColorPalette,{value:a.gradientEnd,onChange:function(v){props.setAttributes({gradientEnd:v});}})
            ])
          ]),
          el(PanelBody,{title:__('Text Labels','irank-calc-cards'),initialOpen:false},[
            el(TextControl,{label:__('Headline','irank-calc-cards'),value:a.questionText,onChange:function(v){props.setAttributes({questionText:v});}}),
            el(TextControl,{label:__('Current Weight Label','irank-calc-cards'),value:a.weightLabel,onChange:function(v){props.setAttributes({weightLabel:v});}}),
            el(TextControl,{label:__('Loss Label','irank-calc-cards'),value:a.lossLabel,onChange:function(v){props.setAttributes({lossLabel:v});}}),
            el(TextControl,{label:__('Before Label','irank-calc-cards'),value:a.beforeLabel,onChange:function(v){props.setAttributes({beforeLabel:v});}}),
            el(TextControl,{label:__('After Label','irank-calc-cards'),value:a.afterLabel,onChange:function(v){props.setAttributes({afterLabel:v});}}),
            el(TextControl,{label:__('CTA Text','irank-calc-cards'),value:a.ctaText,onChange:function(v){props.setAttributes({ctaText:v});}}),
            el(TextControl,{label:__('Timer Text','irank-calc-cards'),value:a.timerText,onChange:function(v){props.setAttributes({timerText:v});}})
          ])
          ,
          el(PanelBody,{title:__('Typography','irank-calc-cards'),initialOpen:false},[
            el('h4',{},__('Headline','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.questionFontFamily,options:[{label:'Nohemi',value:'Nohemi'},{label:'Poppins',value:'Poppins'}],onChange:function(v){props.setAttributes({questionFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.questionFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({questionFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.questionFontSize,onChange:function(v){props.setAttributes({questionFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.questionColor,onChange:function(v){props.setAttributes({questionColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('Current Weight Label','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.weightFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({weightFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.weightFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({weightFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.weightFontSize,onChange:function(v){props.setAttributes({weightFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.weightColor,onChange:function(v){props.setAttributes({weightColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('Loss Label','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.lossFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({lossFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.lossFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({lossFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.lossFontSize,onChange:function(v){props.setAttributes({lossFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.lossColor,onChange:function(v){props.setAttributes({lossColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('Before Label','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.beforeFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({beforeFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.beforeFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({beforeFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.beforeFontSize,onChange:function(v){props.setAttributes({beforeFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.beforeColor,onChange:function(v){props.setAttributes({beforeColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('After Label','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.afterFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({afterFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.afterFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({afterFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.afterFontSize,onChange:function(v){props.setAttributes({afterFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.afterColor,onChange:function(v){props.setAttributes({afterColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('CTA Text','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.ctaFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({ctaFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.ctaFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({ctaFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.ctaFontSize,onChange:function(v){props.setAttributes({ctaFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaColor,onChange:function(v){props.setAttributes({ctaColor:v});}})
            ]),

            el('hr'),
            el('h4',{},__('Timer Text','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:a.timerFontFamily,options:[{label:'Poppins',value:'Poppins'},{label:'Nohemi',value:'Nohemi'}],onChange:function(v){props.setAttributes({timerFontFamily:v});}}),
            el(SelectControl,{label:__('Font Weight','irank-calc-cards'),value:a.timerFontWeight,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({timerFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Font Size','irank-calc-cards'),value:a.timerFontSize,onChange:function(v){props.setAttributes({timerFontSize:v});}}),
            el('div',{},[
              el('label',{},__('Color','irank-calc-cards')),
              el(ColorPalette,{value:a.timerColor,onChange:function(v){props.setAttributes({timerColor:v});}})
            ])
          ])
          ,
          el(PanelBody,{title:__('Buttons','irank-calc-cards'),initialOpen:false},[
            el('h4',{},__('CTA Button','irank-calc-cards')),
            el('div',{},[
              el('label',{},__('Background','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaBg,onChange:function(v){props.setAttributes({ctaBg:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Text Color','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaColor,onChange:function(v){props.setAttributes({ctaColor:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Background','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaHoverBg,onChange:function(v){props.setAttributes({ctaHoverBg:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Text','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaHoverColor,onChange:function(v){props.setAttributes({ctaHoverColor:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Border','irank-calc-cards')),
              el(ColorPalette,{value:a.ctaHoverBorder,onChange:function(v){props.setAttributes({ctaHoverBorder:v});}})
            ]),

            el('hr'),
            el('h4',{},__('Before/After Labels','irank-calc-cards')),
            el('div',{},[
              el('label',{},__('Background','irank-calc-cards')),
              el(ColorPalette,{value:a.labelBg,onChange:function(v){props.setAttributes({labelBg:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Text Color','irank-calc-cards')),
              el(ColorPalette,{value:a.labelColor,onChange:function(v){props.setAttributes({labelColor:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Background','irank-calc-cards')),
              el(ColorPalette,{value:a.labelHoverBg,onChange:function(v){props.setAttributes({labelHoverBg:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Text','irank-calc-cards')),
              el(ColorPalette,{value:a.labelHoverColor,onChange:function(v){props.setAttributes({labelHoverColor:v});}})
            ]),
            el('div',{},[
              el('label',{},__('Hover Border','irank-calc-cards')),
              el(ColorPalette,{value:a.labelHoverBorder,onChange:function(v){props.setAttributes({labelHoverBorder:v});}})
            ])
          ])
        ),
        el('div',{className:'irank-calc-preview',style:{border:'1px dashed #ddd',padding:'16px'}},[
          el('strong',{},__('Weight Loss Calculator Preview','irank-calc-cards')),
          el('div',{}, a.questionText || __('How much weight can you lose','irank-calc-cards')),
          el('div',{}, (a.weightLabel || __('My current weight:','irank-calc-cards')) + ' ' + a.initialWeight + ' ' + a.unit),
          el('div',{}, a.lossLabel || __('Weight loss potential:','irank-calc-cards'))
        ])
      ];
    },
    save: function(){ return null; }
  });
})();
