(()=>{"use strict";var e,n={445:(e,n,r)=>{const t=window.wp.blocks,l=window.React,o=(window.wp.i18n,window.wp.blockEditor);window.wp.apiFetch;const a=window.wp.element,i=window.wp.components,s=JSON.parse('{"UU":"sim/missingformfields"}');(0,t.registerBlockType)(s.UU,{icon:"form",edit:({attributes:e,setAttributes:n})=>{const{type:r}=e,[t,s]=(0,a.useState)((0,l.createElement)(i.Spinner,null));return(0,a.useEffect)((()=>{}),[r]),(0,l.createElement)(l.Fragment,null,(0,l.createElement)(o.InspectorControls,null,(0,l.createElement)(i.Panel,null,(0,l.createElement)(i.PanelBody,null,(0,l.createElement)(i.RadioControl,{label:"Type of fields",selected:r,options:[{label:"Recommended",value:"recommended"},{label:"Mandatory",value:"mandatory"},{label:"Both",value:"all"}],onChange:e=>n({type:e})})))),(0,l.createElement)("div",{...(0,o.useBlockProps)()},wp.element.RawHTML({children:t})))},save:()=>null})}},r={};function t(e){var l=r[e];if(void 0!==l)return l.exports;var o=r[e]={exports:{}};return n[e](o,o.exports,t),o.exports}t.m=n,e=[],t.O=(n,r,l,o)=>{if(!r){var a=1/0;for(d=0;d<e.length;d++){r=e[d][0],l=e[d][1],o=e[d][2];for(var i=!0,s=0;s<r.length;s++)(!1&o||a>=o)&&Object.keys(t.O).every((e=>t.O[e](r[s])))?r.splice(s--,1):(i=!1,o<a&&(a=o));if(i){e.splice(d--,1);var c=l();void 0!==c&&(n=c)}}return n}o=o||0;for(var d=e.length;d>0&&e[d-1][2]>o;d--)e[d]=e[d-1];e[d]=[r,l,o]},t.n=e=>{var n=e&&e.__esModule?()=>e.default:()=>e;return t.d(n,{a:n}),n},t.d=(e,n)=>{for(var r in n)t.o(n,r)&&!t.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:n[r]})},t.o=(e,n)=>Object.prototype.hasOwnProperty.call(e,n),(()=>{var e={57:0,350:0};t.O.j=n=>0===e[n];var n=(n,r)=>{var l,o,a=r[0],i=r[1],s=r[2],c=0;if(a.some((n=>0!==e[n]))){for(l in i)t.o(i,l)&&(t.m[l]=i[l]);if(s)var d=s(t)}for(n&&n(r);c<a.length;c++)o=a[c],t.o(e,o)&&e[o]&&e[o][0](),e[o]=0;return t.O(d)},r=self.webpackChunksim_missing_form_fields=self.webpackChunksim_missing_form_fields||[];r.forEach(n.bind(null,0)),r.push=n.bind(null,r.push.bind(r))})();var l=t.O(void 0,[350],(()=>t(445)));l=t.O(l)})();