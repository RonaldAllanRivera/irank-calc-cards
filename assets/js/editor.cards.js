(function(){
  var __ = wp.i18n.__;
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var Button = wp.components.Button;

  function clone(o){ return JSON.parse(JSON.stringify(o||{})); }

  function renderCardEditor(props, idx){
    var cards = props.attributes.cards || [];
    var c = cards[idx] || {name:'',tagline:'',price:'',benefits:[],badge:'',ctaText:'',ctaUrl:''};
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
          el(Button,{isDestructive:true,onClick:function(){
            var next = cards.slice(); next.splice(idx,1); props.setAttributes({cards:next});
          }}, __('Remove','irank-calc-cards'))
        ])
      ]),
      el(TextControl,{label:__('Name','irank-calc-cards'),value:c.name,onChange:function(v){update('name',v);}}),
      el(TextControl,{label:__('Tagline','irank-calc-cards'),value:c.tagline,onChange:function(v){update('tagline',v);}}),
      el(TextControl,{label:__('Price','irank-calc-cards'),value:c.price,onChange:function(v){update('price',v);}}),
      el(TextareaControl,{label:__('Benefits (one per line)','irank-calc-cards'),value:(c.benefits||[]).join('\n'),onChange:function(v){update('benefits', (v||'').split(/\n+/).filter(function(s){return s.trim().length; }));}}),
      el(TextControl,{label:__('Badge','irank-calc-cards'),value:c.badge,onChange:function(v){update('badge',v);}}),
      el(TextControl,{label:__('CTA Text','irank-calc-cards'),value:c.ctaText,onChange:function(v){update('ctaText',v);}}),
      el(TextControl,{label:__('CTA URL','irank-calc-cards'),value:c.ctaUrl,onChange:function(v){update('ctaUrl',v);}})
    ]);
  }

  registerBlockType('irank/product-cards',{
    title: __('Product Cards','irank-calc-cards'),
    icon: 'index-card',
    category: 'widgets',
    attributes:{
      cards:{type:'array',default:[]}
    },
    edit: function(props){
      var cards = props.attributes.cards || [];
      return [
        el(InspectorControls,{},
          el(PanelBody,{title:__('Cards','irank-calc-cards'),initialOpen:true},[
            cards.map(function(_,i){ return renderCardEditor(props,i); }),
            el(Button,{isPrimary:true,onClick:function(){
              var next = (cards||[]).slice();
              next.push({name:'',tagline:'',price:'',benefits:[],badge:'',ctaText:'',ctaUrl:''});
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
