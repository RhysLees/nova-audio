import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-audio', IndexField)
  app.component('detail-audio', DetailField)
  app.component('form-audio', FormField)
})
