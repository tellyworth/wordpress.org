(window.webpackJsonp=window.webpackJsonp||[]).push([[1],{11:function(t,e,n){}}]),function(t){function e(e){for(var r,u,c=e[0],a=e[1],l=e[2],p=0,s=[];p<c.length;p++)u=c[p],Object.prototype.hasOwnProperty.call(o,u)&&o[u]&&s.push(o[u][0]),o[u]=0;for(r in a)Object.prototype.hasOwnProperty.call(a,r)&&(t[r]=a[r]);for(f&&f(e);s.length;)s.shift()();return i.push.apply(i,l||[]),n()}function n(){for(var t,e=0;e<i.length;e++){for(var n=i[e],r=!0,c=1;c<n.length;c++){var a=n[c];0!==o[a]&&(r=!1)}r&&(i.splice(e--,1),t=u(u.s=n[0]))}return t}var r={},o={0:0},i=[];function u(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,u),n.l=!0,n.exports}u.m=t,u.c=r,u.d=function(t,e,n){u.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},u.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},u.t=function(t,e){if(1&e&&(t=u(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(u.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)u.d(n,r,function(e){return t[e]}.bind(null,r));return n},u.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return u.d(e,"a",e),e},u.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},u.p="";var c=window.webpackJsonp=window.webpackJsonp||[],a=c.push.bind(c);c.push=e,c=c.slice();for(var l=0;l<c.length;l++)e(c[l]);var f=a;i.push([5,1]),n()}([function(t,e){!function(){t.exports=this.wp.element}()},function(t,e,n){var r=n(6),o=n(7),i=n(8),u=n(10);t.exports=function(t,e){return r(t)||o(t,e)||i(t,e)||u()}},function(t,e){!function(){t.exports=this.wp.components}()},function(t,e){!function(){t.exports=this.wp.i18n}()},function(t,e){!function(){t.exports=this.wp.url}()},function(t,e,n){"use strict";n.r(e);var r=n(1),o=n.n(r),i=n(0),u=n(2),c=n(3),a=n(4),l=(n(11),window.wporgLocaleSwitcherConfig||{}),f=function(t){var e=t.externalButton,n=l.initialValue,r=l.options,f=Object(i.useState)(!1),p=o()(f,2),s=p[0],d=p[1],b=Object(i.useState)(!1),y=o()(b,2),w=y[0],h=y[1];return e.addEventListener("click",(function(t){t.preventDefault(),d(!0)})),Object(i.createElement)(i.Fragment,null,s&&Object(i.createElement)(u.Modal,{closeButtonLabel:Object(c.__)("Cancel","wporg"),onRequestClose:function(){return d(!1)},title:Object(c.__)("Change language","wporg")},Object(i.createElement)(u.ComboboxControl,{onChange:function(t){h(t),function(t){var e=window.location.href;window.location=Object(a.addQueryArgs)(e,{locale:t})}(t)},onFilterValueChange:function(){},options:r,value:w||n})))};document.addEventListener("DOMContentLoaded",(function(){var t=document.getElementById("wporg-locale-switcher-container"),e={externalButton:document.getElementById("wp-admin-bar-locale-switcher")};Object(i.render)(Object(i.createElement)(f,e),t)}))},function(t,e){t.exports=function(t){if(Array.isArray(t))return t}},function(t,e){t.exports=function(t,e){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t)){var n=[],r=!0,o=!1,i=void 0;try{for(var u,c=t[Symbol.iterator]();!(r=(u=c.next()).done)&&(n.push(u.value),!e||n.length!==e);r=!0);}catch(t){o=!0,i=t}finally{try{r||null==c.return||c.return()}finally{if(o)throw i}}return n}}},function(t,e,n){var r=n(9);t.exports=function(t,e){if(t){if("string"==typeof t)return r(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?r(t,e):void 0}}},function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}}]);