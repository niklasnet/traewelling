@extends('admin.layout')

@section('title', 'Trip ' . $trip->trip_id)

@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $trip->trip_id }}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{ $trip->category }}</td>
                        </tr>
                        <tr>
                            <th>Internal Number</th>
                            <td>{{ $trip->number }}</td>
                        </tr>
                        <tr>
                            <th>Linename</th>
                            <td>{{ $trip->linename }}</td>
                        </tr>
                        <tr>
                            <th>Journey number</th>
                            <td>{{ $trip->journey_number }}</td>
                        </tr>
                        <tr>
                            <th>Operator</th>
                            <td>{{ $trip->operator?->name }}</td>
                        </tr>
                        <tr>
                            <th>Last refreshed</th>
                            <td>{{ $trip->last_refreshed }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="card-title fs-5">Checkins</h2>
                    @if($trip->checkins->count() === 0)
                        <span class="fw-bold text-danger">No checkins for this trip.</span>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                @foreach($trip->checkins as $checkin)
                                    <tr>
                                        <td>
                                            {{$checkin->user->name}}
                                            <small><a href="{{route('admin.users.user', ['id' => $checkin->user->id])}}">{{'@'.$checkin->user->username}}</a></small>
                                            <br />
                                            <a href="{{route('admin.status.edit', ['statusId' => $checkin->status->id])}}">
                                                #{{ $checkin->status->id }}
                                            </a>
                                        </td>
                                        <td>
                                            {{$checkin->originStation->name}}
                                            <br/>
                                            <small>
                                                dep {{$checkin->origin_stopover->departure_planned->format('H:i')}}
                                                +{{$checkin->origin_stopover->departure_planned->diffInMinutes($checkin->origin_stopover->departure)}}
                                            </small>
                                        </td>
                                        <td>
                                            {{$checkin->destinationStation->name}}
                                            <br/>
                                            <small>
                                                arr {{$checkin->destination_stopover->arrival_planned->format('H:i')}}
                                                +{{$checkin->destination_stopover->arrival_planned->diffInMinutes($checkin->destination_stopover->arrival)}}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title fs-5">Stopovers</h2>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">TRWL-ID</th>
                                <th scope="col">IBNR</th>
                                <th scope="col">Ankunft plan</th>
                                <th scope="col">real</th>
                                <th scope="col">Abfahrt plan</th>
                                <th scope="col">real</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trip->stopovers as $stopover)
                                <tr>
                                    <td>{{$stopover->trainStation?->name}}</td>
                                    <td>{{$stopover->trainStation?->id}}</td>
                                    <td>{{$stopover->trainStation?->ibnr}}</td>
                                    <td>{{$stopover->arrival_planned?->format('H:i')}}</td>
                                    <td>{{$stopover->arrival_real?->format('H:i')}}</td>
                                    <td>{{$stopover->departure_planned->format('H:i')}}</td>
                                    <td>{{$stopover->departure_real?->format('H:i')}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
