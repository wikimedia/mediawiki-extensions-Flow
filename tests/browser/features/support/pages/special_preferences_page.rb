class SpecialPreferencesPage
  include PageObject

  page_url "Special:Preferences"

  link(:beta_features, id: 'preftab-betafeatures')

  checkbox(:flow_beta_feature, id: 'mw-input-wpbeta-feature-flow-user-talk-page')

  button(:save_preferences, id: 'prefcontrol')

  div(:confirmation, text: 'Your preferences have been saved.')
end
