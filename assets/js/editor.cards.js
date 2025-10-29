(function(){
  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var Button = wp.components.Button;
  var MediaUpload = (wp.blockEditor && wp.blockEditor.MediaUpload) || (wp.editor && wp.editor.MediaUpload);
  var SelectControl = wp.components.SelectControl;
  var ColorPalette = (wp.blockEditor && wp.blockEditor.ColorPalette) || (wp.editor && wp.editor.ColorPalette);

  function clone(o){ return JSON.parse(JSON.stringify(o||{})); }

  function renderCardEditor(props, idx){
    var cards = props.attributes.cards || [];
    var c = cards[idx] || {name:'',tagline:'',price:'',priceSuffix:'/month',priceNote:'',benefits:[],badge:'',ctaText:'',ctaUrl:'',imageId:0,imageUrl:''};
    function update(key,val){
      var next = cards.slice();
      next[idx] = clone(c);
      next[idx][key] = val;
      props.setAttributes({cards: next});
    }
    return el('div',{className:'irank-card-editor',style:{border:'1px solid #eee',padding:'12px',marginBottom:'12px'}},[
      el('div',{style:{display:'flex',justifyContent:'space-between',alignItems:'center'}},[
        el('strong',{}, __('Card','irank-calc-cards')+' #'+(idx+1)),
        el('div',{},[
          el(Button,{isSecondary:true,onClick:function(){
            var next = cards.slice();
            if(idx>0){ var t = next[idx-1]; next[idx-1]=next[idx]; next[idx]=t; }
            props.setAttributes({cards:next});
          }}, '↑'),
          el(Button,{isSecondary:true,onClick:function(){
            var next = cards.slice();
            if(idx<next.length-1){ var t = next[idx+1]; next[idx+1]=next[idx]; next[idx]=t; }
            props.setAttributes({cards:next});
          }}, '↓'),
          el(Button,{isSecondary:true,onClick:function(){
            var next = cards.slice();
            var dup = clone(c);
            // Optionally annotate copied name
            if(dup && typeof dup.name === 'string' && dup.name.trim()){ dup.name = dup.name + ' (Copy)'; }
            next.splice(idx+1,0,dup);
            props.setAttributes({cards:next});
          }}, __('Duplicate','irank-calc-cards')),
          el(Button,{isDestructive:true,onClick:function(){
            var next = cards.slice(); next.splice(idx,1); props.setAttributes({cards:next});
          }}, __('Remove','irank-calc-cards'))
        ])
      ]),
      el(TextControl,{label:__('Name','irank-calc-cards'),value:c.name,onChange:function(v){update('name',v);}}),
      el(TextControl,{label:__('Tagline','irank-calc-cards'),value:c.tagline,onChange:function(v){update('tagline',v);}}),
      el(TextControl,{label:__('Price','irank-calc-cards'),value:c.price,onChange:function(v){update('price',v);}}),
      el(TextControl,{label:__('Price Suffix','irank-calc-cards'),help:'/month',value:c.priceSuffix,onChange:function(v){update('priceSuffix',v);}}),
      el(TextControl,{label:__('Price Tagline (below price)','irank-calc-cards'),placeholder:'(everything included)',value:c.priceNote,onChange:function(v){update('priceNote',v);}}),
      el(TextareaControl,{label:__('Benefits (one per line)','irank-calc-cards'),value:(c.benefits||[]).join('\n'),onChange:function(v){update('benefits', (v||'').split(/\n+/).filter(function(s){return s.trim().length; }));}}),
      el(TextControl,{label:__('Badge','irank-calc-cards'),value:c.badge,onChange:function(v){update('badge',v);}}),
      el(TextControl,{label:__('CTA Text','irank-calc-cards'),value:c.ctaText,onChange:function(v){update('ctaText',v);}}),
      el(TextControl,{label:__('CTA URL','irank-calc-cards'),value:c.ctaUrl,onChange:function(v){update('ctaUrl',v);}}),
      el('div',{},[
        el('label',{}, __('Image','irank-calc-cards')),
        c.imageUrl ? el('div',{style:{margin:'8px 0'}},
          el('img',{src:c.imageUrl,alt:c.name||'',style:{maxWidth:'100%',height:'auto',display:'block',border:'1px solid #eee',borderRadius:'8px'}})
        ) : null,
        el(MediaUpload,{onSelect:function(m){ update('imageId',m.id); update('imageUrl',(m.sizes&&m.sizes.medium&&m.sizes.medium.url)||m.url||''); },
          allowedTypes:['image'], value:c.imageId, render:function(o){ return el(Button,{isSecondary:true,onClick:o.open}, c.imageUrl?__('Change Image','irank-calc-cards'):__('Select Image','irank-calc-cards')); }})
      ])
    ]);
  }

  registerBlockType('irank/product-cards',{
    title: __('Product Cards','irank-calc-cards'),
    icon: 'index-card',
    category: 'widgets',
    attributes:{
      cards:{type:'array',default:[]},
      sectionHeader:{type:'string',default:'Choose your path to transformation'},
      sectionHeading:{type:'string',default:'All medications included in price.'},
      sectionSubheading:{type:'string',default:'No hidden pharmacy or lab fees.'},
      // Colors
      cardsBgStart:{type:'string'}, cardsBgEnd:{type:'string'}, cardBg:{type:'string'},
      ctaBg:{type:'string'}, ctaColor:{type:'string'}, ctaHoverBg:{type:'string'}, ctaHoverColor:{type:'string'}, ctaHoverBorder:{type:'string'},
      badgeColor:{type:'string'}, badgeGradStart:{type:'string'}, badgeGradEnd:{type:'string'},
      // Typography
      kickerFontFamily:{type:'string',default:'Poppins'}, kickerFontWeight:{type:'number',default:500}, kickerFontSize:{type:'string',default:'14px'}, kickerColor:{type:'string',default:'#ffffff'}, kickerBorderColor:{type:'string',default:'#ffffff'},
      headingFontFamily:{type:'string',default:'Poppins'}, headingFontWeight:{type:'number',default:600}, headingFontSize:{type:'string',default:'48px'}, headingLineHeight:{type:'string',default:'54px'}, headingColor:{type:'string',default:'#ffffff'},
      subFontFamily:{type:'string',default:'Poppins'}, subFontWeight:{type:'number',default:600}, subFontSize:{type:'string',default:'48px'}, subColor:{type:'string',default:'#FFBB8E'}
    },
    edit: function(props){
      var cards = props.attributes.cards || [];
      return [
        el(InspectorControls,{},
          el(PanelBody,{title:__('Section','irank-calc-cards'),initialOpen:true},[
            el(TextControl,{label:__('Section Header','irank-calc-cards'),value:props.attributes.sectionHeader||'',onChange:function(v){props.setAttributes({sectionHeader:v});}}),
            el(TextControl,{label:__('Heading','irank-calc-cards'),value:props.attributes.sectionHeading||'',onChange:function(v){props.setAttributes({sectionHeading:v});}}),
            el(TextControl,{label:__('Subheading','irank-calc-cards'),value:props.attributes.sectionSubheading||'',onChange:function(v){props.setAttributes({sectionSubheading:v});}})
          ]),
          el(PanelBody,{title:__('Typography','irank-calc-cards'),initialOpen:false},[
            el('h4',{},__('Section Header','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:props.attributes.kickerFontFamily||'Poppins',options:[{label:'Poppins',value:'Poppins'}],onChange:function(v){props.setAttributes({kickerFontFamily:v});}}),
            el(SelectControl,{label:__('Weight','irank-calc-cards'),value:props.attributes.kickerFontWeight||500,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({kickerFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Size','irank-calc-cards'),value:props.attributes.kickerFontSize||'14px',onChange:function(v){props.setAttributes({kickerFontSize:v});}}),
            el('div',{},[ el('label',{},__('Text Color','irank-calc-cards')), el(ColorPalette,{value:props.attributes.kickerColor,onChange:function(v){props.setAttributes({kickerColor:v});}}) ]),
            el('div',{},[ el('label',{},__('Border Color','irank-calc-cards')), el(ColorPalette,{value:props.attributes.kickerBorderColor,onChange:function(v){props.setAttributes({kickerBorderColor:v});}}) ]),

            el('hr'),
            el('h4',{},__('Heading','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:props.attributes.headingFontFamily||'Poppins',options:[{label:'Poppins',value:'Poppins'}],onChange:function(v){props.setAttributes({headingFontFamily:v});}}),
            el(SelectControl,{label:__('Weight','irank-calc-cards'),value:props.attributes.headingFontWeight||600,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({headingFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Size','irank-calc-cards'),value:props.attributes.headingFontSize||'48px',onChange:function(v){props.setAttributes({headingFontSize:v});}}),
            el(TextControl,{label:__('Line Height','irank-calc-cards'),value:props.attributes.headingLineHeight||'54px',onChange:function(v){props.setAttributes({headingLineHeight:v});}}),
            el('div',{},[ el('label',{},__('Color','irank-calc-cards')), el(ColorPalette,{value:props.attributes.headingColor,onChange:function(v){props.setAttributes({headingColor:v});}}) ]),

            el('hr'),
            el('h4',{},__('Subheading','irank-calc-cards')),
            el(SelectControl,{label:__('Font Family','irank-calc-cards'),value:props.attributes.subFontFamily||'Poppins',options:[{label:'Poppins',value:'Poppins'}],onChange:function(v){props.setAttributes({subFontFamily:v});}}),
            el(SelectControl,{label:__('Weight','irank-calc-cards'),value:props.attributes.subFontWeight||600,options:[{label:'Medium (500)',value:500},{label:'Semi Bold (600)',value:600},{label:'Bold (700)',value:700}],onChange:function(v){props.setAttributes({subFontWeight:parseInt(v,10)});}}),
            el(TextControl,{label:__('Size','irank-calc-cards'),value:props.attributes.subFontSize||'48px',onChange:function(v){props.setAttributes({subFontSize:v});}}),
            el('div',{},[ el('label',{},__('Color','irank-calc-cards')), el(ColorPalette,{value:props.attributes.subColor,onChange:function(v){props.setAttributes({subColor:v});}}) ])
          ]),
          el(PanelBody,{title:__('Colors','irank-calc-cards'),initialOpen:false},[
            el(TextControl,{label:__('Section Gradient Start','irank-calc-cards'),value:props.attributes.cardsBgStart||'',onChange:function(v){props.setAttributes({cardsBgStart:v});},help:'#92245A'}),
            el(TextControl,{label:__('Section Gradient End','irank-calc-cards'),value:props.attributes.cardsBgEnd||'',onChange:function(v){props.setAttributes({cardsBgEnd:v});},help:'#92245A'}),
            el(TextControl,{label:__('Card Background','irank-calc-cards'),value:props.attributes.cardBg||'',onChange:function(v){props.setAttributes({cardBg:v});},help:'#ffffff'}),
            el(TextControl,{label:__('CTA BG','irank-calc-cards'),value:props.attributes.ctaBg||'',onChange:function(v){props.setAttributes({ctaBg:v});},help:'#92245A'}),
            el(TextControl,{label:__('CTA Text','irank-calc-cards'),value:props.attributes.ctaColor||'',onChange:function(v){props.setAttributes({ctaColor:v});},help:'#ffffff'}),
            el(TextControl,{label:__('CTA Hover BG','irank-calc-cards'),value:props.attributes.ctaHoverBg||'',onChange:function(v){props.setAttributes({ctaHoverBg:v});},help:'#ffffff'}),
            el(TextControl,{label:__('CTA Hover Text','irank-calc-cards'),value:props.attributes.ctaHoverColor||'',onChange:function(v){props.setAttributes({ctaHoverColor:v});},help:'#000000'}),
            el(TextControl,{label:__('CTA Hover Border','irank-calc-cards'),value:props.attributes.ctaHoverBorder||'',onChange:function(v){props.setAttributes({ctaHoverBorder:v});},help:'#000000'}),
            el(TextControl,{label:__('Badge Gradient Start','irank-calc-cards'),value:props.attributes.badgeGradStart||'',onChange:function(v){props.setAttributes({badgeGradStart:v});},help:'#FD9651'}),
            el(TextControl,{label:__('Badge Gradient End','irank-calc-cards'),value:props.attributes.badgeGradEnd||'',onChange:function(v){props.setAttributes({badgeGradEnd:v});},help:'#F0532C'}),
            el(TextControl,{label:__('Badge Text','irank-calc-cards'),value:props.attributes.badgeColor||'',onChange:function(v){props.setAttributes({badgeColor:v});},help:'#000000'})
          ]),
          el(PanelBody,{title:__('Cards','irank-calc-cards'),initialOpen:true},[
            cards.map(function(_,i){ return renderCardEditor(props,i); }),
            el(Button,{isPrimary:true,onClick:function(){
              var next = (cards||[]).slice();
              next.push({name:'',tagline:'',price:'',priceSuffix:'/month',priceNote:'',benefits:[],badge:'',ctaText:'',ctaUrl:'',imageId:0,imageUrl:''});
              props.setAttributes({cards:next});
            }}, __('Add Card','irank-calc-cards'))
          ])
        ),
        el('div',{className:'irank-cards-preview',style:{border:'1px dashed #ddd',padding:'16px'}},[
          el('strong',{}, __('Product Cards Preview','irank-calc-cards')),
          el('ul',{}, (cards||[]).map(function(c,i){
            return el('li',{key:i}, (c.name||__('Card','irank-calc-cards')+' '+(i+1)) + ' – ' + (c.price||''));
          }))
        ])
      ];
    },
    save: function(){ return null; }
  });
})();
