@extends('layouts.admin.main')
@section('headerTitle', 'Privacy Policy')
@section('pageTitle', 'Privacy Policy')

@section('content')
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-block">
                    
                    <input type="hidden" id="drs" name="drange"/>
                    <input type="hidden" id="delete_value" name="delete_value"/>
                    <div class="table-overflow">
                        <table id="procedure-table" class="table table-lg table-hover" width="100%">
                            <thead>
                                <tr>
                                    <th>*</th>
                                    <th>Name</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
var oTable;
oTable = $('#procedure-table').DataTable({
    processing: true,
    serverSide: true,
    dom: 'lBfrtip',
    order:  [[ 3, "asc" ]],
    pagingType: 'full_numbers',
    buttons: [
        {
            extend: 'print',
            autoPrint: true,
            customize: function ( win ) {
                $(win.document.body)
                    .css( 'padding', '2px' )
                    .prepend(
                        '<img src="{{asset('img/logo.png')}}" style="float:right; top:0; left:0;height: 40px;right: 10px;background: #101010;padding: 8px;border-radius: 4px" /><h5 style="font-size: 9px;margin-top: 0px;"><br/><font style="font-size:14px;margin-top: 5px;margin-bottom:20px;"> Report Concept</font><br/><br/><font style="font-size:8px;margin-top:15px;">{{date('Y-m-d h:i:s')}}</font></h5><br/><br/>'
                    );


                $(win.document.body).find( 'div' )
                    .css( {'padding': '2px', 'text-align': 'center', 'margin-top': '-50px'} )
                    .prepend(
                        ''
                    );

                $(win.document.body).find( 'table' )
                    .addClass( 'compact' )
                    .css( { 'font-size': '9px', 'padding': '2px' } );


            },
            title: '',
            orientation: 'landscape',
            exportOptions: {columns: ':visible'} ,
            text: '<i class="fa fa-print" data-toggle="tooltip" title="" data-original-title="Print"></i>',
            //className: 'btn btn-primary'
        },
        {extend: 'colvis', text: '<i class="fa fa-eye" data-toggle="tooltip" title="" data-original-title="Column visible"></i>'},
        {extend: 'csv', text: '<i class="fa fa-file-excel-o" data-toggle="tooltip" title="" data-original-title="Export CSV"></i>'}
    ],
    //sDom: "<'table-responsive fixed't><'row'<p i>> B",
    sPaginationType: "bootstrap",
    destroy: true,
    responsive: true,
    scrollCollapse: true,
    oLanguage: {
        "sLengthMenu": "_MENU_ ",
        "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
    },
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    ajax: {
    url: '{!! route('privacy-policy.data') !!}',
        data: function (d) {
            d.range = $('input[name=drange]').val();
        }
    },
    columns: [
		{ data: "rownum", name: "rownum" },
		{ data: "name", name: "name" },
		{ data: "created_at", name: "created_at", visible:false },
		{ data: "updated_at", name: "updated_at" },
        { data: "action", name: "action", searchable: false, orderable: false },
    ],
}).on( 'processing.dt', function ( e, settings, processing ) {if(processing){Pace.start();} else {Pace.stop();}});

$("#concept-table_wrapper > .dt-buttons").appendTo("div.export-options-container");

$('#formsearch').submit(function () {
    oTable.search( $('#search-table').val() ).draw();
    return false;
} );

oTable.page.len(25).draw();

</script>
@endpush