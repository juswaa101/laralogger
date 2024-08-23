@foreach ($logs as $log)
    <tr>
        <td>{{ $log['date'] }}</td>
        <td>{{ ucfirst($log['level']) }}</td>
        <td>{{ $log['message'] }}</td>
    </tr>
@endforeach
