// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

import 'core/inplace_editable';
import Notification from 'core/notification';
import Ajax from 'core/ajax';
import {
    get_strings as getStrings,
} from 'core/str';

export const init = (cmid) => {
        registerEventListeners(cmid);
};

/**
 * Function to register and defines event listeners
 * @param {int} cmid
 */
function registerEventListeners(cmid) {

    const deletebtns = document.querySelectorAll('#cardboxx-topics .cardboxx-edittopics-delete-button');
    deletebtns.forEach(btn => {
        const topic = btn.closest('#cardboxx-topic-in-review');
        const topicid = topic.getAttribute('data-topicid');
        const topictitel = topic.getAttribute('data-titel');
        btn.addEventListener('click', () => {
            deletetopic(cmid, topicid, topictitel);
        });
    });

    const editbtns = document.querySelectorAll('#cardboxx-topics .cardboxx-edittopics-edit-button');
    editbtns.forEach(btn => {
        const edit = btn.closest('#cardboxx-topic-in-review');
        const topicid = edit.getAttribute('data-topicid');

        btn.addEventListener('click', () => {
            const topictitel = edit.getAttribute('data-titel');
            edittopic(cmid, topicid, topictitel);
        });
    });

    document.getElementById("cardboxx-submit-new-topic").addEventListener('click', () => {
        savenewtopic(cmid);
    });
}
/**
 * Function to delete a topic from cardboxx instance
 * @param {int} cmid
 * @param {int} topicid
 * @param {string} topictitel
 */
function deletetopic(cmid, topicid, topictitel) {
    getStrings([
        {key: 'deletetopic', component: 'cardboxx'},
        {key: 'deletetopicinfo', component: 'cardboxx', param: "'" + topictitel + "'"},
        {key: 'yes'},
        {key: 'cancel'}
    ])
    .then(strings => {
        Notification.confirm(strings[0], strings[1], strings[2], strings[3], () => {
            Ajax.call([{
                methodname:'mod_cardboxx_deletetopic',
                args: {
                    'topicid': topicid,
                },
                done: () => {
                    document.getElementById("cardboxx-topic-"+topicid).parentElement.parentElement.remove();
                },
                fail: Notification.exception
            }]);
        });
    }).catch(Notification.exception);
}
/**
 * Function to edit an existing topic from the cardboxx instance
 * @param {int} cmid
 * @param {int} topicid
 * @param {string} topictitel
 */
function edittopic(cmid, topicid, topictitel) {

    document.getElementById("cardboxx-topic-"+topicid).classList.add('displaynone');
    document.getElementById("cardboxx-topic-rename-"+topicid).classList.remove('displaynone');
    document.getElementById("cardboxx-changedtopic-"+topicid).value = topictitel;
    document.getElementById("cardboxx-changedtopic-"+topicid).focus();
    document.getElementById("cardboxx-topic-rename-"+topicid).addEventListener('keydown', () => {
        if (window.event.keyCode == 13) {
            var newtopicname = document.getElementById("cardboxx-changedtopic-"+topicid).value;
            if (newtopicname != topictitel){
                Ajax.call([{
                    methodname:'mod_cardboxx_renametopic',
                    args: {
                        'topicid': topicid,
                        'newtopicname': newtopicname,
                    },
                    done: () => {
                        document.getElementById("topictitel-"+topicid).innerHTML = newtopicname;
                        document.getElementById("cardboxx-topic-"+topicid).parentElement.setAttribute('data-titel', newtopicname);
                    },
                    fail: Notification.exception
                }]);
            }
            document.getElementById("cardboxx-topic-"+topicid).classList.remove('displaynone');
            document.getElementById("cardboxx-topic-rename-"+topicid).classList.add('displaynone');
        }
        if (window.event.keyCode == 27) {
            document.getElementById("cardboxx-topic-"+topicid).classList.remove('displaynone');
            document.getElementById("cardboxx-topic-rename-"+topicid).classList.add('displaynone');
        }
    });
    document.getElementById("cardboxx-changedtopic-"+topicid).addEventListener('blur', () => {
        document.getElementById("cardboxx-topic-"+topicid).classList.remove('displaynone');
        document.getElementById("cardboxx-topic-rename-"+topicid).classList.add('displaynone');
    });

}
/**
 * Function to save new topic to the cardboxx instance
 * @param {int} cmid
 */
function savenewtopic (cmid) {
    var newtopic = document.getElementById("create-new-topic").value;
    if (newtopic != "") {
        var goTo = window.location.pathname + '?id=' + cmid + '&action=savenewtopic&newtopic=' + newtopic;
        window.location.href = goTo;
    }
}