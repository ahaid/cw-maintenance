// New task validation
function validateForm()
{
var x=document.forms["taskForm"]["title"].value;
if (x==null || x=="")
  {
  alert("You need to give this task a name.");
   document.forms["taskForm"]["title"].focus();
  return false;
  }
}

// New project validation
function validateTitle()
{
var x=document.forms["projectForm"]["newcat"].value;
if (x==null || x=="")
  {
  alert("You need to give this project a name.");
   document.forms["projectForm"]["newcat"].focus();
  return false;
  }
}


// Search validation
function validateSearch()
{
  var x=document.forms["searchform"]["s"].value;
if (x==null || x=="")
  {
  alert("Please enter a search term.");
   document.forms["searchform"]["s"].focus();
  return false;
  }
}

// Update bio validation
function validateName()
{
var x=document.forms["accountForm"]["first_name"].value;
if (x==null || x=="")
  {
  alert("Woah there! You need to have a first name.");
   document.forms["accountForm"]["first_name"].focus();
  return false;
  }
  var x=document.forms["accountForm"]["email"].value;
if (x==null || x=="")
  {
  alert("You need to specify an email address.");
   document.forms["accountForm"]["email"].focus();
  return false;
  }
}

function deleteTask(url)
{
	if (confirm('Are you sure you want to delete this task?')) {
    window.location.href=url;
  }
  return false;
}

// Count remaining chars available on certain inputs
var max = 100;
$('#title, #newcat').keyup(function() {
	if($(this).val().length > max) {
		$(this).val($(this).val().substr(0, max));
	}
	$('#title-chars').html((max - $(this).val().length) + ' characters left');
});
var descmax = 200;
$('#description').keyup(function() {
	if($(this).val().length > descmax) {
		$(this).val($(this).val().substr(0, descmax));
	}
	$('#desc-chars').html((descmax - $(this).val().length) + ' characters left');
});


// Add alternating classes
$('table tr:odd').addClass('alt');
$('.project:odd').addClass('alt');
$('.report .chart li a:odd').addClass('alt');
$('.my-active-list .task:odd, .my-completed-list .task:odd, .all-active-list .task:odd, .all-completed-list .task:odd, .search-results .task:odd, .my-recent-tasks li:odd').addClass('odd');
$('.my-active-list .task:even, .my-completed-list .task:even, .all-active-list .task:even, .all-completed-list .task:even, .search-results .task:even, .my-recent-tasks li:even').addClass('even');

jQuery(function($) {
	$(".advanced-cats input:radio").click(function() {
	if (this.checked) {
	  $("label.on").removeClass("on");
	  $(this).closest("label").addClass("on");
	}
	});
});

$(".cancel-task").click(function(){
	$('.add-task').hide();
	$('.new-task-toggle').removeClass('orange');
});

// Change text on toggle buttons
$('.new-task-toggle').click(function() {
	$('.mask').toggleClass('mask-show');
});
$('.cancel-task').click(function() {
	$('.mask').toggleClass('mask-show');
})

// Change text on toggle buttons
$('.show-edit-toggle').click(function() {
    $(this).text($(this).text() == 'Edit Task' ? 'Cancel Editing' : 'Edit Task');
	$(".show-edit-toggle").toggleClass("orange");
    return false;
});
$('.show-advanced').click(function() {
    $(this).text($(this).text() == 'Search Filter' ? 'Only Search In:' : 'Search Filter');
    return false;
});
$('.show-more').click(function() {
    $(this).text($(this).text() == 'Show More' ? 'Show Less' : 'Show More');
    return false;
});

// Hide these elements when editing a task
$(".show-edit-toggle").click(function () {
	$(".task-alone, h2, h3").fadeToggle("fast");
});


// Toggles and Effects
$(document).ready(function(){
	// Show the advanced search
	$(".show-advanced, .close-search").click(function () {
		$(".advanced-cats").slideToggle("fast");
	});
	// Show the New task box
	$(".new-task-toggle").click(function(){
		$(".add-task").fadeToggle("fast");
        $( "#title" ).focus();
	});
	// Show the edit task box
	$(".show-edit-toggle").click(function(){
		$(".edit-task").fadeToggle("fast");
	});
	// Show the help topic
	$(".help").click(function(){
		$(".help-topic").fadeToggle("fast");
	});
	// Show the more info box
	$(".show-more").click(function() {
		$(this).next(".more").slideToggle("fast");
  	});
	// Show project delete confirmation box
	$(".show-delete").click(function() {
		$(this).next(".deleter").slideToggle("fast");
  	});
	// Hide project delete confirmation box
	$(".hide-delete").click(function() {
		$(this).closest(".deleter").slideToggle("fast");
  	});
	// Toggle the reassign element
	$("#notify").click(function() {
		$(".notify-elements").slideToggle("fast");
  	});
	// Fadeout project delete confirmation box onclick
	$(".confirm-delete").click(function () {
      $(".deleter").fadeOut(1000);
	  $(this).closest(".project").fadeOut(1000);
    });

	// Fadeout the task when the checkbox is clicked
	$(".checkbox-label").click(function() {
		$(this).closest(".task, .home-task").fadeOut(1000);
  	});

	// Fadeout attachment list item onclick
	$(".delete-yes").click(function () {
	  $(this).closest(".attachments li").fadeOut(1000);
    });

	// Fadein delete file confirmation
	$(".delete-attachment-button").click(function () {
		$(this).closest('.attachments li').find('.delete-file-confirmation').fadeIn(250);
    });

	// Fadeout when 'no' is selected in delete confirmation
	$(".delete-no").click(function () {
	  $(this).closest(".delete-file-confirmation").fadeOut(250);
    });

	// Fadeout message confirmation box
	$(".message .close-message").click(function () {
      $(".message").fadeOut(1000);
    });

	// Mobile menu toggle
	$(".today em").click(function(){
		$(".left").animate({width:'toggle'},100);
	});

	// Add class to main content area when hovering over the .left-pane element
	$(".left-pane").hover(function(){
		$(".dashboard, .content").toggleClass("mover");
		$(".left-pane").toggleClass("mover");
	});

	// Add class to mobile nav button
	$('.toggle-menu').click(function() {
		$('.toggle-menu').toggleClass('closer');
		$('.left').toggleClass('show-me');
	})


	// Toggles between active, completed and authors (my-active) list
	$( ".all-completed-list" ).hide();

	$(".all-active").click(function(){
		$(".all-active-list").fadeIn("fast");
		$(".my-completed-list, .my-active-list, .all-completed-list").fadeOut( "fast");

		$(".all-active").addClass("focused");
		$(".my-completed, .my-active, .all-completed").removeClass("focused");
		$(".my-completed, .my-active, .all-completed").removeClass("pink");
	});

	$(".all-completed").click(function(){
		$(".all-completed-list").fadeIn("fast");
		$(".all-active-list, .my-completed-list, .my-active-list").fadeOut("fast");

		$(".all-completed").addClass("focused");
		$(".my-active, .my-completed, .all-active").removeClass("focused");
		$(".my-active, .my-completed, .all-active").removeClass("pink");
	});

    // Show sorting options on mobile
	if($(window).width() < 960){
		$(".sort-tasks").click(function(){
			$(".sort-tasks ul").fadeToggle("fast");
		});
	};


	// Prevent body scrolling when a modal is in focus
	$(".new-task-toggle, .cancel-task").click(function(){
	  $("body").toggleClass("stop-scrolling");
	});



    // Move right pame comments when scrolling
    if($(window).width() > 960){

        $(window).scroll(function(){
            if($(window).scrollTop() > 73) {
                $(".comment-pos").addClass("comment-pos-repos");
            }
            if($(window).scrollTop() < 73) {
                $(".comment-pos").removeClass("comment-pos-repos");
            }
        });
    }


});
