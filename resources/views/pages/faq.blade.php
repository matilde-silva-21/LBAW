@extends('layouts.app')
<!--Section: FAQ-->
@section('content')

<div class="container bg-body" id="faq-container">
    <h1 class="text-center">FAQ</h1>
    <p class="text-center mb-5">
        Find the answers for the most frequently asked questions below
    </p>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="far fa-paper-plane text-primary pe-2"></i> Can I search for an specific Event?</h6>
            <p>
                <strong><u>Absolutely!</u></strong> On the top of our home page you'll find a search bar, where you can input for example the name of the event you're looking for,
                or you can also search by City or Country.

            </p>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="fas fa-pen-alt text-primary pe-2"></i> Can I cancel the my presence in a Event I previously enrolled? </h6>
            <p>
                <strong><u>Yes, it is possible!</u></strong> You can cancel your presence in an Event as long as you do it with at least 24 hours before the Event starts.
            </p>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="fas fa-user text-primary pe-2"></i> How can I create a new Event?
            </h6>
            <p>
                To be able to create an Event, you need to be a <strong>registered User</strong> and be <strong>logged In</strong> on our website. After that you can navigate
                to your profile where you'll find a button to create a new Event.
            </p>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="fas fa-rocket text-primary pe-2"></i> What can I do if I need more specific help that I can't find here?
            </h6>
            <p>
                You can always contact us directly, you can find that information on the <a href="{{route('aboutUs.index')}}">About Us</a> page.
            </p>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="fas fa-home text-primary pe-2"></i> How do I know who can enroll in my Events?
                question?
            </h6>
            <p>As long as they are authenticated and the event privacy is set to public, <strong><u>anyone</u></strong> can enroll in your events. </p>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <h6 class="mb-3 text-primary"><i class="fas fa-book-open text-primary pe-2"></i> If I make a mistake creating an Event, can I change some information about it?</h6>
            <p>
                Of course! We understand that sometimes we make mistakes, so you can always edit your Events. You can do that by navigating to your profile and clicking on the event you
                want to edit.
            </p>
        </div>
    </div>
</div>
<!--Section: FAQ-->

@endsection
