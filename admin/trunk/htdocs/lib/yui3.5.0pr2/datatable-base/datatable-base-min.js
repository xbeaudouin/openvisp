/*
YUI 3.5.0pr2 (build 4560)
Copyright 2011 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("datatable-base",function(a){a.DataTable.Base=a.Base.create("datatable",a.Widget,[a.DataTable.Core],null,{ATTRS:{headerView:{value:a.DataTable.HeaderView},bodyView:{value:a.DataTable.BodyView}}});a.DataTable=a.mix(a.Base.create("datatable",a.DataTable.Base,[]),a.DataTable);},"3.5.0pr2",{requires:["datatable-core","base-build","widget","datatable-head","datatable-body"]});