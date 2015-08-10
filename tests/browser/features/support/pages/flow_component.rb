class FlowComponent
  include PageObject

  div(:board, class: 'flow-board')
  div(:header, class: 'flow-ui-boardDescriptionWidget-content')
end
