$(document).on("click", ".join", function (event) {
    event.preventDefault();

    const source = getDataset(event);
    $("#checkinModal").modal("show", function (event) {
        const modal = $(this);
        modal
            .find(".modal-title")
            .html(
                source.trwlLinename +
                ' <i class="fas fa-arrow-alt-circle-right"></i> ' +
                source.trwlStopName
            );
        modal.find("#input-tripID").val(source.trwlTripId);
        modal.find("#input-destination").val(source.trwlDestination);
        modal.find("#input-arrival").val(source.trwlArrival);
        modal.find("#input-start").val(source.trwlStart);
        modal.find("#input-departure").val(source.trwlDeparture);
        // case for small number of events
        modal.find("#event_check").each(function () {
            $(this).prop("checked", $(this).val() === source.trwlEventId);
        });
        // case for large number of events
        modal.find("#event-dropdown").val(source.trwlEventId);
    });
});

document.querySelectorAll('.status .like').forEach((likeButton) => {
    likeButton.addEventListener('click', (pointerEvent) => {
        if (!pointerEvent.target.attributes.href.value === "#") {
            //Unauthenticated users should not like the status
            return;
        }

        let statusId = pointerEvent.srcElement.closest('.status').dataset.trwlId;

        let spanLikeCount = document.querySelector('.status[data-trwl-id=\'' + statusId + '\'] .likeCount');

        event.preventDefault();
        event.stopPropagation();

        if (pointerEvent.target.className === "like far fa-star") {
            Status.like(statusId)
                .then(response => {
                    if (!response.ok) {
                        return;
                    }

                    pointerEvent.target.className = "like fas fa-star animated bounceIn";
                    response.json().then((data) => {
                        let likeCount           = data.data.count;
                        spanLikeCount.innerText = likeCount;
                        if (likeCount === 0) {
                            spanLikeCount.classList.add("d-none");
                        } else {
                            spanLikeCount.classList.remove("d-none");
                        }
                    });
                });
            return;
        }

        Status.unlike(statusId)
            .then(response => {
                if (!response.ok) {
                    return;
                }
                pointerEvent.target.className = "like far fa-star";

                response.json().then((data) => {
                    let likeCount           = data.data.count;
                    spanLikeCount.innerText = likeCount;
                    if (likeCount === 0) {
                        spanLikeCount.classList.add("d-none");
                    } else {
                        spanLikeCount.classList.remove("d-none");
                    }
                });
            });
    })
});

$(document).on("click", ".follow", function (event) {
    event.preventDefault();
    let userId         = event.target.dataset["userid"];
    let privateProfile = event.target.dataset["private"];
    let following      = event.target.dataset["following"];

    if (privateProfile === "no") {
        if (following === "no") {
            $.ajax({
                method: "POST",
                url: urlFollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "yes";
                event.target.classList.add("btn-danger");
                event.target.classList.remove("btn-primary");
                event.target.innerText = window.translUnfollow;
            });
        } else {
            $.ajax({
                method: "POST",
                url: urlUnfollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "no";
                event.target.classList.add("btn-primary");
                event.target.classList.remove("btn-danger");
                event.target.innerText = window.translFollow;
            });
        }
    } else {
        if (following === "no") {
            $.ajax({
                method: "POST",
                url: urlFollowRequest,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                event.target.dataset["following"] = "yes";
                event.target.classList.add("disabled");
                event.target.innerText = window.translPending;
            });
        } else {
            $.ajax({
                method: "POST",
                url: urlUnfollow,
                data: {follow_id: userId, _token: token}
            }).done(function () {
                location.reload();
            });
        }
    }
});

$(document).on("click", ".disconnect", function (event) {
    event.preventDefault();

    let provider = event.target.dataset["provider"];
    $.ajax({
        method: "POST",
        url: urlDisconnect,
        data: {provider: provider, _token: token},
        success: function () {
            location.reload();
        },
        error: function (request) {
            bootstrap_alert.danger(request.responseText);
        }
    });
});

$(document).on("click", ".trwl-share", function (event) {
    event.preventDefault();

    let shareText = getDataset(event).trwlShareText;
    let shareUrl  = getDataset(event).trwlShareUrl;

    if (navigator.share) {
        navigator.share({
            title: "Träwelling",
            text: shareText,
            url: shareUrl
        })
            .catch(console.error);
    } else {
        navigator.clipboard.writeText(shareText + " " + shareUrl)
            .then(() => {
                window.notyf.success('Copied to clipboard');
            });
    }

});

function getDataset(event) {
    let target = event.target.dataset;
    let parent = event.target.parentElement.dataset;

    return _.size(event.target.dataset) ? target : parent;
}
