import { NgModule } from '@angular/core';
import { RateIconsPipe, RateColorsPipe, RateWordsPipe } from '../pipe/rate-icons';

@NgModule({
  imports: [
    // dep modules
  ],
  declarations: [
    RateIconsPipe,
    RateColorsPipe,
    RateWordsPipe
  ],
  exports: [
    RateIconsPipe,
    RateColorsPipe,
    RateWordsPipe
  ]
})
export class OfanCoreShareIconsModule {}