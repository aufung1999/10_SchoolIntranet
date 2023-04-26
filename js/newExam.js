function changeQuestionType(elm) {
    let questionElm = elm.closest(".question");
    let specific = questionElm.querySelectorAll(".type-specific");
    console.log(questionElm.dataset.index);
    for (let i = 0; i < specific.length; i++) {
        specific[i].parentElement.removeChild(specific[i]);
    }
    console.log(elm.value);
    if (elm.value.trim() != '') {
        let newScore = document.createElement("tr");
        newScore.classList.add("type-specific");
        newScore.innerHTML = ' <td class="form-label"><label for="question_score[' + questionElm.dataset.index + ']">Score</label></td> <td class="form-input"><input type="number" name="question_score[' + questionElm.dataset.index + ']" /></td> <td class="form-msg"></td>';
        elm.closest("tr").parentNode.insertBefore(newScore, elm.closest("tr").nextSibling);
        if (elm.value == 'fill') {
            let newFillText = document.createElement("tr");
            newFillText.classList.add("type-specific");
            newFillText.innerHTML = '<td class="form-label"><label for="question_title[' + questionElm.dataset.index + ']">Question</label></td><td class="form-input"><textarea style="min-width: 300px;min-height:80px;" name="question_title[' + questionElm.dataset.index + ']" class="question_title" placeholder="Make a fill in the [blank] question like this. If you want to indicate it is not a blank, you may just use \\[this\\] type of expression."></textarea></td><td class="form-msg"></td>';
            elm.closest("tr").parentNode.insertBefore(newFillText, elm.closest("tr").nextSibling);
        } else {
            if (elm.value == 'tf') {
                let newOption = document.createElement("tr");
                newOption.classList.add("type-specific");
                newOption.innerHTML = '<td class="form-label">Correct answer</td><td class="form-input"><input type="radio" name="choice[' + questionElm.dataset.index + ']" id="choice[' + questionElm.dataset.index + ']_true" class="choice" value="true"><label for="choice[' + questionElm.dataset.index + ']_true">True</label><input type="radio" name="choice[' + questionElm.dataset.index + ']" id="choice[' + questionElm.dataset.index + ']_false" class="choice" value="false"><label for="choice[' + questionElm.dataset.index + ']_false">False</label></td><td class="form-msg"></td>';
                elm.closest("tr").parentNode.insertBefore(newOption, elm.closest("tr").nextSibling);
            } else if (elm.value == 'choice') {
                let newBtnAddOption = document.createElement("tr");
                newBtnAddOption.classList.add("type-specific");
                newBtnAddOption.innerHTML = '<td colspan="2" style="text-align: center;">+ Add more option</td>';
                elm.closest("tr").parentNode.insertBefore(newBtnAddOption, elm.closest("tr").nextSibling);
                addOption(newBtnAddOption);
                newBtnAddOption.setAttribute("onclick", "addOption(this);");
            }
            let newText = document.createElement("tr");
            newText.classList.add("type-specific");
            newText.innerHTML = '<td class="form-label"><label for="question_title[' + questionElm.dataset.index + ']">Question</label></td><td class="form-input"><input type="text" name="question_title[' + questionElm.dataset.index + ']" class="question_title"></td><td class="form-msg"></td>';
            elm.closest("tr").parentNode.insertBefore(newText, elm.closest("tr").nextSibling);
        }
    }
}

function addOption(elm) {
    let questionElm = elm.closest(".question");
    let newOption = document.createElement("tr");
    let optionLength = questionElm.querySelectorAll(".answer_text").length;
    newOption.classList.add("type-specific");
    newOption.innerHTML = '<td class="form-label"><label for="answer_text[' + questionElm.dataset.index + '][' + optionLength + ']" data-index="' + optionLength + '">Option ' + (optionLength + 1) + '</label></td><td class="form-input"><input type="text" name="answer_text[' + questionElm.dataset.index + '][' + optionLength + ']" class="answer_text"><input type="checkbox" name="is_answer[' + questionElm.dataset.index + '][' + optionLength + ']" id="is_answer[' + questionElm.dataset.index + '][' + optionLength + ']" class="is_answer"><label for="is_answer[' + questionElm.dataset.index + '][' + optionLength + ']">Option ' + (optionLength + 1) + ' is answer</label></td><td class="form-msg"></td>';
    elm.parentNode.insertBefore(newOption, elm);
}

function addQuestion(elm) {
    let newQuestion = document.createElement("li");
    let questionLength = elm.parentNode.querySelectorAll(".question").length;
    newQuestion.classList.add("box-item");
    newQuestion.classList.add("question");
    newQuestion.dataset.index = questionLength;
    newQuestion.innerHTML = '<p class="item-title">Question ' + (questionLength + 1) + '</p> <table style="width:100%;" class="box-form"> <tr> <td class="form-label"><label for="question_type[' + questionLength + ']">Type</label></td> <td class="form-input"><select name="question_type[' + questionLength + ']" class="question_type" onchange="changeQuestionType(this);"> <option value="" selected="selected"></option> <option value="tf">True/False</option> <option value="choice">Multiple choice</option> <option value="text">Short text</option> <option value="fill">Fill in the blank</option> </select></td><td class="form-msg"></td> </tr>   <tr> <td colspan="2" class="box-button"><button onclick="deleteQuestion(this);">Delete</button></td> </tr> </table>';
    elm.parentNode.insertBefore(newQuestion, elm);
}

function deleteQuestion(elm) {
    let questionElm = elm.closest(".question");
    let listElm = questionElm.parentNode;
    listElm.removeChild(questionElm);
    questionElm = listElm.querySelectorAll(".question");
    for (let i = 0; i < questionElm.length; i++) {
        const oldIndex = questionElm[i].dataset.index;
        console.log(i + ":" + oldIndex);
        if (oldIndex != i) {
            questionElm[i].dataset.index = i;
            questionElm[i].querySelector(".item-title").innerHTML = "Question " + (i + 1);
            let renameElm = questionElm[i].querySelectorAll("label, input, select, textarea");
            for (let j = 0; j < renameElm.length; j++) {
                if (typeof renameElm[j].attributes.for !== "undefined") {
                    let oldFor = renameElm[j].attributes.for.value;
                    renameElm[j].attributes.for.value = oldFor.replace("[" + oldIndex + "]", "[" + i + "]");
                }
                if (typeof renameElm[j].attributes.name !== "undefined") {
                    let oldName = renameElm[j].attributes.name.value;
                    renameElm[j].attributes.name.value = oldName.replace("[" + oldIndex + "]", "[" + i + "]");
                }
                if (typeof renameElm[j].attributes.id !== "undefined") {
                    let oldId = renameElm[j].attributes.id.value;
                    renameElm[j].attributes.id.value = oldId.replace("[" + oldIndex + "]", "[" + i + "]");
                }
            }
        }
    }
}