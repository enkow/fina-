<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <style type="text/css">
        html {
            width:100%;
            margin:0;
        }

        body {
            width:100%;
            margin:0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size:10px;
        }

        table {
            margin-left: 3px;
            margin-right: 3px;
            width: 100%;
        }

        thead tr {
            font-size: 11px;
            font-weight: 500;
        }

        th, td {
            padding-top: 10px;
            padding-left:3px;
            padding-right:3px;
        }
    </style>
<body>
<table>
    <thead>
    <tr>
        @foreach($headings as $heading)
            <td>{{ $heading }}</td>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($data as $entry)
        <tr>
            @foreach($entry as $field)
                <td>{{ $field }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>