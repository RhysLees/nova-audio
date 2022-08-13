import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-nova-audio', IndexField)
  app.component('detail-nova-audio', DetailField)
  app.component('form-nova-audio', FormField)
})
